<?php

namespace App\Controller;

use App\Entity\SessionStudyClassPresence;
use App\Entity\StudentClassRegistered;
use App\Entity\StudyClass;
use App\Form\StudyClassType;
use App\Model\ApiResponseTrait;
use App\Repository\PaymentRepository;
use App\Repository\RoomRepository;
use App\Repository\SessionRepository;
use App\Repository\SessionStudyClassPresenceRepository;
use App\Repository\StudentClassRegisteredRepository;
use App\Repository\StudentRepository;
use App\Repository\StudyClassRepository;
use App\Repository\TeacherRepository;
use App\Service\StudyClassService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\Exception\NotEncodableValueException;
use Symfony\Component\Serializer\SerializerInterface;

#[IsGranted('ROLE_TEACHER')]
#[Route('/study-class')]
class StudyClassController extends AbstractController
{
    use ApiResponseTrait;

    public function __construct(
        private StudentClassRegisteredRepository $studentClassRegisteredRepository,
        private PaymentRepository $paymentRepository,
        private StudentRepository $studentRepository,
        private EntityManagerInterface $entityManager,
        private SessionStudyClassPresenceRepository $sessionStudyClassPresenceRepository,
        private StudyClassRepository $studyClassRepository,
        private SessionRepository $sessionRepository,
        private TeacherRepository $teacherRepository,
        private RoomRepository $roomRepository
    ) {
    }

    #[Route('/', name: 'app_study_class_index', options: ['expose' => true], methods: ['GET'])]
    public function index(StudyClassRepository $studyClassRepository, Request $request): Response
    {
        $page = $request->query->getInt('page', 1);
        $limit = 10;
        $search = $request->query->get('search', '');

        $queryBuilder = $studyClassRepository->createQueryBuilder('sc');

        if (!empty($search)) {
            $queryBuilder
                ->where('sc.name LIKE :search')
                ->andWhere('sc.active = true')
                ->orWhere('sc.level LIKE :search')
                ->orWhere('sc.speciality LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }

        $queryBuilder->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit);

        $paginator = new Paginator($queryBuilder);

        // Fetch student counts for each class
        $studentCounts = $studyClassRepository->getStudentCounts();
        $studentCountMap = [];
        foreach ($studentCounts as $count) {
            $studentCountMap[$count['id']] = $count['studentCount'];
        }

        return $this->render('study_class/index.html.twig', [
            'study_classes' => $paginator,
            'current_page' => $page,
            'total_pages' => ceil(count($paginator) / $limit),
            'search' => $search,
            'student_count_map' => $studentCountMap,
        ]);
    }


    #[Route('/all-study-class', name: 'study_class_list', options: ['expose' => true], methods: ['GET'])]
    public function getAllStudyClass(): JsonResponse
    {
        $allStudyClass = $this->studyClassRepository->findBy(['active' => true], ['name' => 'ASC']);
        $studentCounts = $this->studyClassRepository->getStudentCounts();
        $studentCountMap = [];
        foreach ($studentCounts as $count) {
            $studentCountMap[$count['id']] = $count['studentCount'];
        }
        return $this->json([
            'studyClass' => $allStudyClass,
            'studentCountMap' => $studentCountMap,
        ], 200, [], ['groups' => 'read_study_class']);
    }

    #[Route('/new', name: 'app_study_class_new', options: ['expose' => true], methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {

        $allTeachers = $this->teacherRepository->findAll();
        $rooms = $this->roomRepository->findAll();

        return $this->render('study_class/new.html.twig', [
            'allTeachers' => $allTeachers,
            'rooms' => $rooms,
            'userCurrent' => $this->getUser(),
        ]);
    }

    #[Route(
        '/{id}',
        name: 'app_study_class_show',
        requirements: ['id' => '\d+'],
        options: ['expose' => true],
        methods: ['GET']
    )]
    public function show(StudyClass $studyClass): Response
    {
        $studentsNotInStudyClass = $this->studentRepository->findStudentsNotInStudyClass($studyClass);
        $studentsInStudyClass = $this->studentClassRegisteredRepository->findStudentsInStudyClass($studyClass);
        return $this->render('study_class/show.html.twig', [
            'studyClass' => $studyClass,
            'studentsInStudyClass' => $studentsInStudyClass,
            'studentsNotInStudyClass' => $studentsNotInStudyClass,
        ]);
    }

    #[Route('/new-student-to-class/{id}', name: 'new_student_to_class', options: ['expose' => true], methods: ['POST'])]
    public function newStudentToClass(Request $request, StudyClass $studyClass): Response
    {
        try {
            $data = json_decode($request->getContent(), true);
            if (array_key_exists('studentIds', $data)) {
                foreach ($data['studentIds'] as $studentId) {
                    $student = $this->studentRepository->find($studentId);
                    if ($student) {
                        $studentClassRegistered = new StudentClassRegistered($studyClass, $student);
                        $this->entityManager->persist($studentClassRegistered);
                        $futureSessions = $this->sessionRepository->findFutureSessions($studentClassRegistered->getStudyClass());
                        foreach ($futureSessions as $session){
                            $sessionStudyClassPresence = new SessionStudyClassPresence($student, $session);
                            $this->entityManager->persist($sessionStudyClassPresence);
                        }
                    }
                }
            }
            $this->entityManager->flush();
            return $this->apiResponse('Inscription request');
        } catch (\Exception $e) {
            return $this->apiErrorResponse('JSON invalide.', Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/delete-student-from-class/{id}', name: 'delete_student_from_class', options: ['expose' => true], methods: ['POST'])]
    public function deleteStudentFromClass(Request $request, StudentClassRegistered $studentClassRegistered): Response
    {
        $this->entityManager->remove($studentClassRegistered);
        $futureSessions = $this->sessionRepository->findFutureSessions($studentClassRegistered->getStudyClass());
        foreach ($futureSessions as $session){
            $sessionStudyClassPresence = $this->sessionStudyClassPresenceRepository->findOneBy(['student' => $studentClassRegistered->getStudent(), 'session' => $session]);
            if ($sessionStudyClassPresence){
                $this->entityManager->remove($sessionStudyClassPresence);
            }
        }
        $this->entityManager->flush();
        return $this->apiResponse('Inscription request');
    }

    #[Route('/deactivate-student-from-class/{id}', name: 'deactivate_student_from_class', options: ['expose' => true], methods: ['POST'])]
    public function deactivateStudentFromClass(Request $request, StudentClassRegistered $studentClassRegistered): Response
    {
        $studentClassRegistered->setActive(false);
        $this->entityManager->flush();
        return $this->apiResponse('Inscription request');
    }

    #[Route('/{id}/edit', name: 'app_study_class_edit', options: ['expose' => true], methods: ['GET','POST'])]
    public function edit(Request $request, StudyClass $studyClass, EntityManagerInterface $entityManager): Response
    {
        $allTeachers = $this->teacherRepository->findAll();
        $rooms = $this->roomRepository->findAll();
        return $this->render('study_class/edit.html.twig', [
            'studyClass' => $studyClass,
            'allTeachers' => $allTeachers,
            'rooms' => $rooms,
            'userCurrent' => $this->getUser(),
        ]);
    }

    #[Route('/{id}', name: 'app_study_class_delete', options: ['expose' => true], methods: ['DELETE'])]
    public function delete(Request $request, StudyClass $studyClass, EntityManagerInterface $entityManager): Response
    {
        $paymentsExist = $this->paymentRepository->findBy(['studyClass' => $studyClass]);
        if ($paymentsExist){
            return $this->apiErrorResponse('Impossible de supprimer cette classe car des paiements y sont rattachés.');
        }
        $studentsRegistered = $this->studentClassRegisteredRepository->findBy(['studyClass' => $studyClass]);
        foreach ($studentsRegistered as $studentRegistered){
            $this->entityManager->remove($studentRegistered);
        }
        $entityManager->remove($studyClass);
        $entityManager->flush();
        return $this->apiResponse('Suppression effectuée' );
    }


    #[Route('/planning', name: 'app_planning_study_class', options: ['expose' => true], methods: ['GET', 'POST'])]
    public function planningPage(Request $request, EntityManagerInterface $entityManager): Response
    {
        return $this->render('study_class/planning_study_class.html.twig');
    }

    #[Route(
        '/list',
        name: 'list_study_class_filtered',
        options: ['expose' => true],
        methods: ['GET']
    )]
    public function listFiltered(SerializerInterface $serializer): Response
    {
        // Récupère toutes les StudyClass
        $classes = $this->studyClassRepository->findBy([
            'active'     => true,
        ]);

        // Sérialise en JSON en réutilisant vos groupes de sérialisation
        $json = $serializer->serialize(
            $classes,
            'json',
            ['groups' => 'read_study_class'] // ou le groupe que vous voulez
        );

        return new Response(
            $json,
            Response::HTTP_OK,
            ['Content-Type' => 'application/json']
        );
    }


    #[Route('/save-data/{id}', name: 'save_data_study_class', options: ['expose' => true], methods: ['POST'])]
    public function saveData(
        int $id,
        Request $request,
        StudyClassService $studyClassService
    ): Response {
        // Récupérer l’entité (ou 404)
        $studyClass = $this->studyClassRepository->findOneBy(['id' => $id]);

        if (!$studyClass) {
            return $this->apiErrorResponse('Classe introuvable.', Response::HTTP_NOT_FOUND);
        }

        // 1. Décoder le JSON
        try {
            $data = json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR);
        } catch (NotEncodableValueException $e) {

            return $this->apiErrorResponse('JSON invalide.', Response::HTTP_BAD_REQUEST);
        }
        // 2. Déléguer au service
        $errors = $studyClassService->updateFromArray($studyClass, $data);
        if (null !== $errors) {
            return $this->apiErrorResponse((string)$errors, Response::HTTP_BAD_REQUEST);
        }

        // 3. Répondre avec l’entité mise à jour
        return $this->apiResponse($studyClass);
    }

    #[Route('/save-data-create/', name: 'create_study_class', options: ['expose' => true], methods: ['POST'])]
    public function saveDataForCreate(
        Request $request,
        StudyClassService $studyClassService,
        EntityManagerInterface $em
    ): Response {
        $studyClass = new StudyClass();

        try {
            $data = json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR);
        } catch (NotEncodableValueException $e) {
            return $this->apiErrorResponse('JSON invalide.', Response::HTTP_BAD_REQUEST);
        }

        // Déléguer au service pour setter les données
        $errors = $studyClassService->updateFromArray($studyClass, $data);
        if (null !== $errors) {
            return $this->apiErrorResponse((string)$errors, Response::HTTP_BAD_REQUEST);
        }

        // 🔹 Persister + flush pour générer l’ID
        $em->persist($studyClass);
        $em->flush();

        // 🔹 Retourner une réponse JSON avec l’ID
        return $this->json([
            'id' => $studyClass->getId(),
            'message' => 'Classe créée avec succès'
        ], Response::HTTP_CREATED);
    }
}
