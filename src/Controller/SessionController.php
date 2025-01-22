<?php

namespace App\Controller;

use App\Entity\Room;
use App\Entity\Session;
use App\Entity\SessionStudyClassPresence;
use App\Entity\StudentClassRegistered;
use App\Entity\StudyClass;
use App\Entity\Teacher;
use App\Form\SessionType;
use App\Model\ApiResponseTrait;
use App\Repository\RoomRepository;
use App\Repository\SessionRepository;
use App\Repository\SessionStudyClassPresenceRepository;
use App\Repository\StudentClassRegisteredRepository;
use App\Repository\StudyClassRepository;
use App\Repository\TeacherRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_TEACHER')]
#[Route('/session')]
class SessionController extends AbstractController
{
    // create a constructor with the required fields
    use ApiResponseTrait;

    public function __construct(
        private EntityManagerInterface $entityManager,
        public StudentClassRegisteredRepository $studentClassRegisteredRepository,
        private SessionStudyClassPresenceRepository $sessionStudyClassPresenceRepository,
        private SessionRepository $sessionRepository
    ) {
    }
    #[Route('/', name: 'app_session_index', options: ['expose' => true], methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager, Request $request): Response
    {
        $page = $request->query->getInt('page', 1); // Current page, default is 1
        $limit = 10; // Limit results per page
        $search = $request->query->get('search', ''); // Search filter

        if ($this->isGranted('ROLE_MANAGER')) {
            $paginator = $this->sessionRepository->findSessionsWithPaginationAndSearch($page, $limit, $search);
        } else {
            $paginator = $this->sessionRepository->findSessionsWithPaginationAndSearch($page, $limit, $search,$this->getUser());
        }
        //$paginator = $this->sessionRepository->findSessionsWithPaginationAndSearch($page, $limit, $search,$this->getUser());

        return $this->render('session/index.html.twig', [
            'sessions' => $paginator,
            'current_page' => $page,
            'total_pages' => ceil(count($paginator) / $limit),
            'search' => $search
        ]);
    }

    #[Route('/new', name: 'app_session_new',options: ['expose' => true], methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, Security $security): Response
    {
        $session = new Session();
        $user = $this->getUser();
        $isManager = $security->isGranted('ROLE_MANAGER'); // Vérifie si l'utilisateur est Manager

        // Créer le formulaire avec l'option 'is_manager'
        $form = $this->createForm(SessionType::class, $session, [
            'is_manager' => $isManager,
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Si l'utilisateur est un Teacher, définir automatiquement le Teacher
            if (!$isManager) {
                $teacher = $entityManager->getRepository(Teacher::class)->findOneBy(['user' => $user]);
                $session->setTeacher($teacher);
            }

            $entityManager->persist($session);
            $allStudentsToAddASession = $this->studentClassRegisteredRepository->findStudentsInStudyClass($session->getStudyClass());
            /** @var StudentClassRegistered $student */
            foreach ($allStudentsToAddASession as $student) {
                $sessionStudyClassPresence = new SessionStudyClassPresence($student->getStudent(), $session);
                $entityManager->persist($sessionStudyClassPresence);
            }
            $entityManager->flush();

            return $this->redirectToRoute('app_session_show', ['id' => $session->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('session/new.html.twig', [
            'session' => $session,
            'form' => $form,
            'is_manager' => $isManager,
        ]);
    }

    #[Route('/session/api/data', name: 'app_session_create_data',options: ['expose' => true], methods: ['GET'])]
    public function getCreateData(
        RoomRepository $roomRepository,
        StudyClassRepository $classRepository,
        TeacherRepository $teacherRepository
    ): Response
    {
        $rooms = $roomRepository->findAll();
        $classes = $classRepository->findAll();
        $teachers = $teacherRepository->findAll();

        $roomsArray = array_map(fn(Room $r) => [
            'id' => $r->getId(),
            'name' => $r->getName()
        ], $rooms);

        $classesArray = array_map(fn(StudyClass $c) => [
            'id' => $c->getId(),
            'name' => $c->getName()
        ], $classes);

        $teachersArray = array_map(fn(Teacher $t) => [
            'id' => $t->getId(),
            'fullName' => $t->getFirstName() . ' ' . $t->getLastName()
        ], $teachers);

        return $this->json([
            'rooms'     => $roomsArray,
            'classes'   => $classesArray,
            'teachers'  => $teachersArray,
            'isManager' => $this->isGranted('ROLE_MANAGER'),
        ]);
    }

    #[Route('/session/api/create', name: 'app_session_create_api',options: ['expose' => true], methods: ['POST'])]
    public function createSession(
        Request $request,
        EntityManagerInterface $entityManager,
        StudentClassRegisteredRepository $studentClassRegisteredRepository,
        RoomRepository $roomRepository,
        StudyClassRepository $classRepository,
        TeacherRepository $teacherRepository
    ): Response
    {
        $data = json_decode($request->getContent(), true);

        $session = new Session();

        // Convertir les strings en \DateTime
        $startTime = !empty($data['startTime']) ? new \DateTimeImmutable($data['startTime']) : new \DateTimeImmutable();
        $endTime   = !empty($data['endTime']) ? new \DateTimeImmutable($data['endTime']) : new \DateTimeImmutable();

        $session->setStartTime($startTime);
        $session->setEndTime($endTime);

        // Récupération de la room
        if (!empty($data['roomId'])) {
            $room = $roomRepository->find($data['roomId']);
            $session->setRoom($room);
        }

        // Récupération de la classe
        if (!empty($data['studyClassId'])) {
            $studyClass = $classRepository->find($data['studyClassId']);
            $session->setStudyClass($studyClass);
        }

        // Gestion Teacher en fonction du rôle
        $isManager = $this->isGranted('ROLE_MANAGER');
        if ($isManager && !empty($data['teacherId'])) {
            $teacher = $teacherRepository->find($data['teacherId']);
            $session->setTeacher($teacher);
        } else {
            // Si pas manager, on associe le professeur lié au user courant
            $teacher = $teacherRepository->findOneBy(['user' => $this->getUser()]);
            $session->setTeacher($teacher);
        }

        $entityManager->persist($session);

        // Si la studyClass est définie, on crée les SessionStudyClassPresence
        if ($session->getStudyClass()) {
            $allStudents = $studentClassRegisteredRepository->findStudentsInStudyClass($session->getStudyClass());
            foreach ($allStudents as $studentRegister) {
                $presence = new SessionStudyClassPresence($studentRegister->getStudent(), $session);
                $entityManager->persist($presence);
            }
        }

        $entityManager->flush();

        return $this->json([
            'success' => true,
            'id' => $session->getId(),
        ]);
    }

    #[Route('/delete-student-from-session/{id}', name: 'delete_student_from_session', options: ['expose' => true], methods: ['POST'])]
    public function deleteStudentFromClass(Request $request, SessionStudyClassPresence $sessionStudyClassPresence): Response
    {
        $this->entityManager->remove($sessionStudyClassPresence);
        $this->entityManager->flush();
        return $this->apiResponse('Inscription request');
    }

    #[Route('/mark-student-absent-in-session/{id}', name: 'mark_student_absent_in_session', options: ['expose' => true], methods: ['POST'])]
    public function markStudentAbsentInSession(SessionStudyClassPresence $sessionStudyClassPresence): Response
    {
        $sessionStudyClassPresence->setIsPresent(false);
        $this->entityManager->flush();

        return $this->json(['message' => "L'étudiant a été marqué comme absent."], Response::HTTP_OK);
    }

    #[Route('/mark-student-present-in-session/{id}', name: 'mark_student_present_in_session', options: ['expose' => true], methods: ['POST'])]
    public function markStudentPresentInSession(SessionStudyClassPresence $sessionStudyClassPresence): Response
    {
        $sessionStudyClassPresence->setIsPresent(true);
        $this->entityManager->flush();

        return $this->json(['message' => "L'étudiant a été marqué comme présent."], Response::HTTP_OK);
    }

    #[Route('/{id}', name: 'app_session_show', options: ['expose' => true],methods: ['GET'])]
    public function show(Session $session): Response
    {
        $allStudentsSessionSession = $this->sessionStudyClassPresenceRepository->findBy(['session' => $session]);
        return $this->render('session/show.html.twig', [
            'session' => $session,
            'studentsSession' => $allStudentsSessionSession,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_session_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Session $session, EntityManagerInterface $entityManager, Security $security): Response
    {
        $user = $this->getUser();
        $isManager = $security->isGranted('ROLE_MANAGER'); // Vérifiez si l'utilisateur est Manager

        // Créer le formulaire avec l'option 'is_manager' et 'is_edit'
        $form = $this->createForm(SessionType::class, $session, [
            'is_manager' => $isManager,
            'is_edit' => true, // On est en mode édition
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_session_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('session/edit.html.twig', [
            'session' => $session,
            'form' => $form,
            'is_manager' => $isManager, // Passer la variable pour Twig
        ]);
    }

    #[Route('/{id}', name: 'app_session_delete', methods: ['POST'])]
    public function delete(Request $request, Session $session, EntityManagerInterface $entityManager): Response
    {
        $allStudentsSessionPresences = $this->sessionStudyClassPresenceRepository->findBy(['session' => $session]);
        foreach ($allStudentsSessionPresences as $studentSessionPresence) {
            $entityManager->remove($studentSessionPresence);
        }
        if ($this->isCsrfTokenValid('delete' . $session->getId(), $request->request->get('_token'))) {
            $entityManager->remove($session);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_session_index', [], Response::HTTP_SEE_OTHER);
    }
}
