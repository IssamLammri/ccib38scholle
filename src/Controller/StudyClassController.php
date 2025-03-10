<?php

namespace App\Controller;

use App\Entity\SessionStudyClassPresence;
use App\Entity\StudentClassRegistered;
use App\Entity\StudyClass;
use App\Form\StudyClassType;
use App\Model\ApiResponseTrait;
use App\Repository\SessionRepository;
use App\Repository\SessionStudyClassPresenceRepository;
use App\Repository\StudentClassRegisteredRepository;
use App\Repository\StudentRepository;
use App\Repository\StudyClassRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_TEACHER')]
#[Route('/study-class')]
class StudyClassController extends AbstractController
{
    use ApiResponseTrait;

    public function __construct(
        private StudentClassRegisteredRepository $studentClassRegisteredRepository,
        private StudentRepository $studentRepository,
        private EntityManagerInterface $entityManager,
        private SessionStudyClassPresenceRepository $sessionStudyClassPresenceRepository,
        private SessionRepository $sessionRepository
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
            'student_count_map' => $studentCountMap, // Add the map to the template
        ]);
    }



    #[Route('/new', name: 'app_study_class_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $studyClass = new StudyClass();
        $form = $this->createForm(StudyClassType::class, $studyClass);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($studyClass);
            $entityManager->flush();

            return $this->redirectToRoute('app_study_class_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('study_class/new.html.twig', [
            'study_class' => $studyClass,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_study_class_show', options: ['expose' => true], methods: ['GET'])]
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
        $form = $this->createForm(StudyClassType::class, $studyClass);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_study_class_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('study_class/edit.html.twig', [
            'study_class' => $studyClass,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_study_class_delete', methods: ['POST'])]
    public function delete(Request $request, StudyClass $studyClass, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$studyClass->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($studyClass);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_study_class_index', [], Response::HTTP_SEE_OTHER);
    }
}
