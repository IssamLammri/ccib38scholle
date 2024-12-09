<?php

namespace App\Controller;

use App\Entity\Session;
use App\Entity\SessionStudyClassPresence;
use App\Entity\StudentClassRegistered;
use App\Form\SessionType;
use App\Model\ApiResponseTrait;
use App\Repository\SessionStudyClassPresenceRepository;
use App\Repository\StudentClassRegisteredRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/session')]
#[IsGranted('ROLE_USER')]
class SessionController extends AbstractController
{
    // create a constructor with the required fields
    use ApiResponseTrait;

    public function __construct(
        private EntityManagerInterface $entityManager,
        public StudentClassRegisteredRepository $studentClassRegisteredRepository,
        private SessionStudyClassPresenceRepository $sessionStudyClassPresenceRepository
    ) {
    }
    #[Route('/', name: 'app_session_index', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager, Request $request): Response
    {
        $page = $request->query->getInt('page', 1); // Current page, default is 1
        $limit = 10; // Limit results per page
        $search = $request->query->get('search', ''); // Search filter

        $queryBuilder = $entityManager->getRepository(Session::class)
            ->createQueryBuilder('s')
            ->join('s.room', 'r')
            ->join('s.teacher', 't')
            ->join('s.studyClass', 'sc');

        // Search by room name, teacher name, or study class if search is present
        if (!empty($search)) {
            $queryBuilder
                ->where('r.name LIKE :search')
                ->orWhere('t.lastName LIKE :search')
                ->orWhere('sc.name LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }

        $queryBuilder
            ->setFirstResult(($page - 1) * $limit) // Offset
            ->setMaxResults($limit); // Limit

        $paginator = new Paginator($queryBuilder);

        return $this->render('session/index.html.twig', [
            'sessions' => $paginator,
            'current_page' => $page,
            'total_pages' => ceil(count($paginator) / $limit),
            'search' => $search
        ]);
    }


    #[Route('/new', name: 'app_session_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $session = new Session();
        $form = $this->createForm(SessionType::class, $session);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($session);
            $allStudentsToAddASession = $this->studentClassRegisteredRepository->findStudentsInStudyClass($session->getStudyClass());
            /** @var StudentClassRegistered $student */
            foreach ($allStudentsToAddASession as $student) {
                $sessionStudyClassPresence = new SessionStudyClassPresence($student->getStudent(), $session);
                $entityManager->persist($sessionStudyClassPresence);
            }
            $entityManager->flush();

            return $this->redirectToRoute('app_session_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('session/new.html.twig', [
            'session' => $session,
            'form' => $form,
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

    #[Route('/{id}', name: 'app_session_show', methods: ['GET'])]
    public function show(Session $session): Response
    {
        $allStudentsSessionSession = $this->sessionStudyClassPresenceRepository->findBy(['session' => $session]);
        return $this->render('session/show.html.twig', [
            'session' => $session,
            'studentsSession' => $allStudentsSessionSession,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_session_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Session $session, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(SessionType::class, $session);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_session_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('session/edit.html.twig', [
            'session' => $session,
            'form' => $form,
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
