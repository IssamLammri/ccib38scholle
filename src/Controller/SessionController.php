<?php

namespace App\Controller;

use App\Entity\Session;
use App\Entity\SessionStudyClassPresence;
use App\Entity\StudentClassRegistered;
use App\Entity\Teacher;
use App\Form\SessionType;
use App\Model\ApiResponseTrait;
use App\Repository\SessionRepository;
use App\Repository\SessionStudyClassPresenceRepository;
use App\Repository\StudentClassRegisteredRepository;
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
    #[Route('/', name: 'app_session_index', methods: ['GET'])]
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

    #[Route('/new', name: 'app_session_new', methods: ['GET', 'POST'])]
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
