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
use App\Repository\StudentRepository;
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
        private SessionRepository $sessionRepository,
        private StudentRepository $studentRepository
    ) {
    }
    #[Route('/all-session', name: 'app_session_index', options: ['expose' => true], methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('session/all-sessions.html.twig');
    }


    #[Route('/', name: 'app_session_today', options: ['expose' => true], methods: ['GET'])]
    public function sessionOfDay(): Response
    {
        return $this->render('session/index.html.twig');
    }

    #[Route('/presences', name: 'app_session_presences', options: ['expose' => true], methods: ['GET'])]
    public function presencesAllSessions(): Response
    {
        return $this->render('session/presences_sessions.html.twig');
    }

    /**
     * Retourne toutes les sessions filtrées (sans pagination) et les statistiques par mois.
     *
     * Exemple d'URL : /session/api/sessions?search=xxx&month=3&classId=2&speciality=math&teacherId=5
     */
    // Dans ApiSessionController.php, modifiez getSessions() :
    #[Route('/sessions', name: 'api_sessions', options: ['expose' => true], methods: ['GET'])]
    public function getSessions(
        Request $request,
        StudyClassRepository $studyClassRepository,
        TeacherRepository $teacherRepository
    ): Response {
        // -----------------------------
        // Filtres existants
        // -----------------------------
        $search = trim((string) $request->query->get('search', ''));

        $monthRaw = $request->query->get('month');
        $month = (is_numeric($monthRaw) && (int)$monthRaw >= 1 && (int)$monthRaw <= 12) ? (int)$monthRaw : null;

        $classIdRaw = $request->query->get('classId');
        $classId = (is_numeric($classIdRaw) && (int)$classIdRaw > 0) ? (int)$classIdRaw : null;

        $teacherIdRaw = $request->query->get('teacherId');
        $teacherId = (is_numeric($teacherIdRaw) && (int)$teacherIdRaw > 0) ? (int)$teacherIdRaw : null;

        $speciality = trim((string) $request->query->get('speciality', ''));
        $speciality = $speciality !== '' ? $speciality : null;

        // -----------------------------
        // ✅ Nouveaux filtres
        // -----------------------------
        $schoolYear = (string) ($request->query->get('schoolYear') ?: StudyClass::SCHOOL_YEAR_ACTIVE);

        $dateFrom = $request->query->get('dateFrom'); // ex: "2026-01-01"
        $dateTo   = $request->query->get('dateTo');   // ex: "2026-01-31"

        // ✅ nouveau filtre classType (Arabe / Soutien scolaire / Autre)
        $classType = trim((string) $request->query->get('classType', ''));
        $classType = $classType !== '' ? $classType : null;

        $user = $this->getUser();

        // -----------------------------
        // Sessions (ROLE_MANAGER => toutes)
        // -----------------------------
        $sessions = $this->sessionRepository->findSessionsWithSearch(
            $search,
            $month,
            $classId,
            $speciality,
            $teacherId,
            $this->isGranted('ROLE_MANAGER') ? null : $user,
            $schoolYear,
            $dateFrom,
            $dateTo,
            $classType
        );

        // -----------------------------
        // Sérialisation simplifiée des sessions
        // -----------------------------
        $sessionsArray = [];
        foreach ($sessions as $session) {
            $sessionsArray[] = [
                'id' => $session->getId(),
                'startTime' => $session->getStartTime() ? $session->getStartTime()->format('c') : null,
                'endTime' => $session->getEndTime() ? $session->getEndTime()->format('c') : null,
                'room' => $session->getRoom() ? [
                    'id' => $session->getRoom()->getId(),
                    'name' => $session->getRoom()->getName(),
                ] : null,
                'teacher' => $session->getTeacher() ? [
                    'id' => $session->getTeacher()->getId(),
                    'firstName' => $session->getTeacher()->getFirstName(),
                    'lastName' => $session->getTeacher()->getLastName(),
                ] : null,
                'studyClass' => $session->getStudyClass() ? [
                    'id' => $session->getStudyClass()->getId(),
                    'name' => $session->getStudyClass()->getName(),
                    'speciality' => $session->getStudyClass()->getSpeciality(),
                    'schoolYear' => $session->getStudyClass()->getSchoolYear(),
                    'classType' => $session->getStudyClass()->getClassType(),
                ] : null,
                'presenceCount' => $this->sessionStudyClassPresenceRepository->countWithPresenceFilled($session),
            ];
        }

        // -----------------------------
        // Récupération dynamique des classes
        // -----------------------------
        $classesArray = [];
        foreach ($studyClassRepository->findBy(['active' => true]) as $studyClass) {
            $classesArray[] = [
                'id' => $studyClass->getId(),
                'name' => $studyClass->getName(),
                'speciality' => $studyClass->getSpeciality(),
                'schoolYear' => $studyClass->getSchoolYear(),
                'classType' => $studyClass->getClassType(),
            ];
        }

        // -----------------------------
        // Récupération enseignants
        // -----------------------------
        $teachersArray = [];
        foreach ($teacherRepository->findAll() as $teacher) {
            $teachersArray[] = [
                'id' => $teacher->getId(),
                'firstName' => $teacher->getFirstName(),
                'lastName' => $teacher->getLastName(),
            ];
        }

        // -----------------------------
        // JSON response
        // -----------------------------
        return $this->json([
            'sessions' => $sessionsArray,
            'classes' => $classesArray,
            'teachers' => $teachersArray,
            'isAdmin' => $this->isGranted('ROLE_ADMIN'),

            'activeSchoolYear' => StudyClass::SCHOOL_YEAR_ACTIVE,
            'schoolYears' => StudyClass::SCHOOL_YEARS,

            // ✅ options front pour classType
            'classTypes' => [
                StudyClass::CLASS_TYPE_ARABE,
                StudyClass::CLASS_TYPE_SOUTIEN,
                StudyClass::CLASS_TYPE_AUTRE,
            ],

            'filters' => [
                'search' => $search,
                'month' => $month,
                'classId' => $classId,
                'speciality' => $speciality,
                'teacherId' => $teacherId,
                'schoolYear' => $schoolYear,
                'dateFrom' => $dateFrom,
                'dateTo' => $dateTo,
                'classType' => $classType,
            ],
        ]);
    }


    #[Route('/sessions-today', name: 'api_sessions_today', options: ['expose' => true], methods: ['GET'])]
    public function getSessionsToday(
        Request $request,
        StudyClassRepository $studyClassRepository,
        TeacherRepository $teacherRepository,
        EntityManagerInterface $em
    ): Response {
        $user = $this->getUser();

        // --- Fenêtre "aujourd'hui" en Europe/Paris ---
        $tzParis = new \DateTimeZone('Europe/Paris');
        $startParis = (new \DateTime('today', $tzParis))->setTime(0, 0, 0);
        $endParis   = (clone $startParis)->modify('+1 day'); // demain 00:00:00

        $start = $startParis;
        $end   = $endParis;

        // --- QueryBuilder: sessions qui chevauchent la journée courante ---
        $qb = $em->createQueryBuilder()
            ->select('s', 'r', 't', 'c')
            ->from('App\Entity\Session', 's')
            ->leftJoin('s.room', 'r')
            ->leftJoin('s.teacher', 't')
            ->leftJoin('s.studyClass', 'c')
            ->andWhere('s.startTime < :end')                 // commence avant fin de journée
            ->andWhere('(s.endTime IS NULL OR s.endTime >= :start)') // et se termine après début de journée (ou pas de fin)
            ->setParameter('start', $start)
            ->setParameter('end', $end)
            ->orderBy('s.startTime', 'ASC');

        // Restriction prof / manager
        if (!$this->isGranted('ROLE_MANAGER') && !$this->isGranted('ROLE_ADMIN')) {
            $qb->andWhere('t.user = :user')
                ->setParameter('user', $user);
        }

        $sessions = $qb->getQuery()->getResult();

        // --- Sérialisation : format "naïf" (pas de Z) pour éviter tout décalage côté front ---
        $sessionsArray = [];
        foreach ($sessions as $session) {
            $sessionsArray[] = [
                'id' => $session->getId(),
                'startTime' => $session->getStartTime() ? $session->getStartTime()->format('Y-m-d\TH:i:s') : null,
                'endTime'   => $session->getEndTime()   ? $session->getEndTime()->format('Y-m-d\TH:i:s')   : null,
                'room' => $session->getRoom() ? [
                    'id' => $session->getRoom()->getId(),
                    'name' => $session->getRoom()->getName(),
                ] : null,
                'teacher' => $session->getTeacher() ? [
                    'id' => $session->getTeacher()->getId(),
                    'firstName' => $session->getTeacher()->getFirstName(),
                    'lastName' => $session->getTeacher()->getLastName(),
                ] : null,
                'studyClass' => $session->getStudyClass() ? [
                    'id' => $session->getStudyClass()->getId(),
                    'name' => $session->getStudyClass()->getName(),
                    'speciality' => $session->getStudyClass()->getSpeciality(),
                ] : null,
            ];
        }

        // (facultatif) listes pour UI
        $classesArray = [];
        foreach ($studyClassRepository->findBy([  'active'     => true,]) as $studyClass) {
            $classesArray[] = [
                'id' => $studyClass->getId(),
                'name' => $studyClass->getName()
            ];
        }
        $teachersArray = [];
        foreach ($teacherRepository->findAll() as $teacher) {
            $teachersArray[] = [
                'id' => $teacher->getId(),
                'firstName' => $teacher->getFirstName(),
                'lastName' => $teacher->getLastName()
            ];
        }

        return $this->json([
            'sessions' => $sessionsArray,
            'classes' => $classesArray,
            'teachers' => $teachersArray,
            'isAdmin' => $this->isGranted('ROLE_ADMIN'),
        ]);
    }
    private function resolveTeacherIdIfOnlyTeacher(TeacherRepository $teacherRepo): ?int
    {
        // ✅ si admin/superadmin => accès global
        if ($this->isGranted('ROLE_ADMIN') || $this->isGranted('ROLE_MANAGER')) {
            return null;
        }

        // ✅ si teacher (et pas admin) => filtre
        if ($this->isGranted('ROLE_TEACHER')) {
            $user = $this->getUser();
            if (!$user) {
                return null;
            }

            /** @var Teacher|null $teacher */
            $teacher = $teacherRepo->findOneBy(['user' => $user]);
            if (!$teacher) {
                throw $this->createAccessDeniedException("Aucun teacher lié à cet utilisateur.");
            }

            return $teacher->getId();
        }

        return null;
    }

    private function parseFromTo(Request $request): array
    {
        $fromRaw = $request->query->get('from');
        $toRaw   = $request->query->get('to');

        $from = null;
        $to   = null;

        if ($fromRaw !== null && trim((string)$fromRaw) !== '') {
            $from = new \DateTimeImmutable(trim((string)$fromRaw) . ' 00:00:00');
        }
        if ($toRaw !== null && trim((string)$toRaw) !== '') {
            $to = new \DateTimeImmutable(trim((string)$toRaw) . ' 23:59:59');
        }

        return ['from' => $from, 'to' => $to];
    }

    #[Route(
        '/sessions-presences/by-student',
        name: 'api_presences_sessions_by_student',
        options: ['expose' => true],
        methods: ['GET']
    )]
    public function getPresencesByStudent(
        Request $request,
        SessionStudyClassPresenceRepository $repo,
        TeacherRepository $teacherRepo
    ): Response {
        $page  = max(1, (int) $request->query->get('page', 1));
        $limit = min(100, max(1, (int) $request->query->get('limit', 30)));

        // studyClassId (optionnel)
        $studyClassIdRaw = $request->query->get('studyClassId');
        $studyClassId = ($studyClassIdRaw !== null && (string)$studyClassIdRaw !== '')
            ? (int) $studyClassIdRaw
            : null;

        // classType (optionnel)
        $classTypeRaw = $request->query->get('classType');
        $classType = ($classTypeRaw !== null && trim((string)$classTypeRaw) !== '')
            ? trim((string)$classTypeRaw)
            : null;

        // q (optionnel) => recherche nom/prénom
        $qRaw = $request->query->get('q');
        $q = ($qRaw !== null && trim((string)$qRaw) !== '')
            ? trim((string)$qRaw)
            : null;

        // ✅ teacher filter si ROLE_TEACHER only
        $teacherId = $this->resolveTeacherIdIfOnlyTeacher($teacherRepo);

        // ✅ filtre date
        ['from' => $from, 'to' => $to] = $this->parseFromTo($request);

        // ✅ IMPORTANT: si pas de classe choisie => regrouper par service (classType)
        if ($studyClassId === null) {
            $result = $repo->paginateByStudentServiceStats(
                $page,
                $limit,
                $classType,
                $q,
                $teacherId,
                $from,
                $to
            );
        } else {
            // regroupement par classe (student + studyClass)
            $result = $repo->paginateByStudentStats(
                $page,
                $limit,
                $studyClassId,
                $classType,
                $q,
                $teacherId,
                $from,
                $to
            );
        }

        $total = (int) ($result['total'] ?? 0);
        $pages = (int) max(1, ceil($total / $limit));

        return $this->json([
            'students' => $result['items'] ?? [],
            'meta' => [
                'page' => $page,
                'limit' => $limit,
                'total' => $total,
                'pages' => $pages,
                'hasNext' => $page < $pages,
                'hasPrev' => $page > 1,
            ],
        ]);
    }

    #[Route(
        '/sessions-presences/by-student/{studentId}/history',
        name: 'api_presences_student_history',
        requirements: ['studentId' => '\d+'],
        options: ['expose' => true],
        methods: ['GET']
    )]
    public function getStudentPresenceHistory(
        int $studentId,
        Request $request,
        SessionStudyClassPresenceRepository $repo,
        TeacherRepository $teacherRepo
    ): Response {
        $page  = max(1, (int) $request->query->get('page', 1));
        $limit = min(100, max(1, (int) $request->query->get('limit', 20)));

        $classTypeRaw = $request->query->get('classType');
        $classType = ($classTypeRaw !== null && trim((string)$classTypeRaw) !== '')
            ? trim((string)$classTypeRaw)
            : null;

        $teacherId = $this->resolveTeacherIdIfOnlyTeacher($teacherRepo);

        ['from' => $from, 'to' => $to] = $this->parseFromTo($request);

        $result = $repo->paginateHistoryByStudent(
            $studentId,
            $page,
            $limit,
            $classType,
            $teacherId,
            $from,
            $to
        );

        $total = (int) ($result['total'] ?? 0);
        $pages = (int) max(1, ceil($total / $limit));

        return $this->json([
            'items' => $result['items'] ?? [],
            'meta' => [
                'page' => $page,
                'limit' => $limit,
                'total' => $total,
                'pages' => $pages,
                'hasNext' => $page < $pages,
                'hasPrev' => $page > 1,
            ],
        ]);
    }

    #[Route(
        '/study-classes',
        name: 'api_studyclass_index',
        options: ['expose' => true],
        methods: ['GET']
    )]
    public function listStudyClasses(
        Request $request,
        StudyClassRepository $studyClassRepository,
        TeacherRepository $teacherRepo
    ): Response {
        $schoolYear = StudyClass::SCHOOL_YEAR_ACTIVE; // "2025/2026"

        $classTypeRaw = $request->query->get('classType');
        $classType = ($classTypeRaw !== null && trim((string)$classTypeRaw) !== '')
            ? trim((string)$classTypeRaw)
            : null;

        $teacherId = $this->resolveTeacherIdIfOnlyTeacher($teacherRepo);

        // ✅ Option simple: si teacher only -> classes où il est principalTeacher
        $criteria = ['schoolYear' => $schoolYear];
        if ($classType !== null) {
            $criteria['classType'] = $classType;
        }
        if ($teacherId !== null) {
            $criteria['principalTeacher'] = $teacherId;
        }

        $classes = $studyClassRepository->findBy($criteria, ['name' => 'ASC',  'active'     => true,]);

        $items = array_map(static function (StudyClass $c) {
            return [
                'id' => $c->getId(),
                'name' => $c->getName(),
                'level' => $c->getLevel(),
                'speciality' => $c->getSpeciality(),
                'classType' => $c->getClassType(),
                'schoolYear' => $c->getSchoolYear(),
            ];
        }, $classes);

        return $this->json([
            'items' => $items,
            'meta' => [
                'schoolYear' => $schoolYear,
                'classType' => $classType,
                'count' => count($items),
            ],
        ]);
    }


    /**
     * Supprime une session ainsi que ses présences associées.
     *
     * Exemple d'URL : DELETE /session/api/delete/{id}
     */
    #[Route('/delete/{id}', name: 'api_session_delete', options: ['expose' => true], methods: ['DELETE'])]
    public function deleteSession(Session $session): Response
    {
        // Suppression des présences associées
        $presences = $this->sessionStudyClassPresenceRepository->findBy(['session' => $session]);
        foreach ($presences as $presence) {
            $this->entityManager->remove($presence);
        }
        // Suppression de la session
        $this->entityManager->remove($session);
        $this->entityManager->flush();

        return $this->json(['success' => true]);
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
            $allStudentsToAddASession = $this->studentClassRegisteredRepository->findStudentsActiveInStudyClass($session->getStudyClass());
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
        StudyClassRepository $studyClassRepository,
        TeacherRepository $teacherRepository
    ): Response
    {
        $rooms = $roomRepository->findAll();
        $classes = $studyClassRepository->findBy(['schoolYear' => StudyClass::SCHOOL_YEAR_ACTIVE,  'active'     => true,]);
        $teachers = $teacherRepository->findAll();

        $roomsArray = array_map(fn(Room $r) => [
            'id' => $r->getId(),
            'name' => $r->getName()
        ], $rooms);

        $classesArray = array_map(fn(StudyClass $c) => [
            'id' => $c->getId(),
            'name' => $c->getName(),
            'level' => $c->getLevel(),
            'speciality' => $c->getSpeciality(),
            'principal_teacher' => $c->getPrincipalTeacher()?->getId(),
            'school_year' => $c->getSchoolYear()
        ], $classes);

        $teachersArray = array_map(fn(Teacher $t) => [
            'id' => $t->getId(),
            'fullName' => $t->getFirstName() . ' ' . $t->getLastName(),
            'speciality' => $t->getSpecialities()
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
        StudyClassRepository $studyClassRepository,
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
            $studyClass = $studyClassRepository->find($data['studyClassId']);
            $session->setStudyClass($studyClass);
        }

        // Gestion Teacher en fonction du rôle
        $isManager = $this->isGranted('ROLE_MANAGER');
        if ($isManager && !empty($data['teacherId'])) {
            $teacher = $teacherRepository->find($data['teacherId']);
        } else {
            // Si pas manager, on associe le professeur lié au user courant
            $teacher = $teacherRepository->findOneBy(['user' => $this->getUser()]);
        }
        $session->setTeacher($teacher);
        $entityManager->persist($session);

        // Si la studyClass est définie, on crée les SessionStudyClassPresence
        if ($session->getStudyClass()) {
            $allStudents = $studentClassRegisteredRepository->findStudentsActiveInStudyClass($session->getStudyClass());
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

    #[Route('/{id}', name: 'app_session_show', options: ['expose' => true], methods: ['GET'])]
    public function show(Session $session): Response
    {
        $allStudentsSessionSession = $this->sessionStudyClassPresenceRepository->findBy(['session' => $session]);
        return $this->render('session/show.html.twig', [
            'session' => $session,
            'studentsSession' => $allStudentsSessionSession,
            'currentUser' => $this->getUser(),
        ]);
    }

    #[Route('/{id}/edit', name: 'app_session_edit', options: ['expose' => true], methods: ['GET', 'POST'])]
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
            'currentUser' => $user,
            'form' => $form,
            'is_manager' => $isManager, // Passer la variable pour Twig
        ]);
    }

    #[Route('/api/edit-options', name: 'api_session_edit_options', options: ['expose' => true], methods: ['GET'])]
    public function editOptions(
        TeacherRepository $teacherRepository,
        RoomRepository $roomRepository
    ): Response {
        $teachers = $teacherRepository->findAll();
        $rooms = $roomRepository->findAll();

        $teachersArray = array_map(static fn(Teacher $t) => [
            'id' => $t->getId(),
            'firstName' => $t->getFirstName(),
            'lastName' => $t->getLastName(),
        ], $teachers);

        $roomsArray = array_map(static fn(Room $r) => [
            'id' => $r->getId(),
            'name' => $r->getName(),
        ], $rooms);

        return $this->json([
            'teachers' => $teachersArray,
            'rooms' => $roomsArray,
        ]);
    }


    #[Route('/api/{id}', name: 'api_session_update', options: ['expose' => true], methods: ['PUT', 'PATCH'])]
    public function updateSession(
        Request $request,
        Session $session,
        TeacherRepository $teacherRepository,
        RoomRepository $roomRepository,
        EntityManagerInterface $entityManager,
        Security $security
    ): Response {
        $data = json_decode($request->getContent(), true) ?? [];

        // --------- Sécurité : teacher ne peut modifier que ses sessions ----------
        $isManager = $security->isGranted('ROLE_MANAGER') || $security->isGranted('ROLE_ADMIN');

        if (!$isManager) {
            $currentTeacher = $teacherRepository->findOneBy(['user' => $this->getUser()]);
            if (!$currentTeacher || $session->getTeacher()?->getId() !== $currentTeacher->getId()) {
                throw $this->createAccessDeniedException("Vous n'avez pas le droit de modifier cette session.");
            }
        }

        // --------- startTime / endTime ----------
        if (!empty($data['startTime'])) {
            try {
                $session->setStartTime(new \DateTimeImmutable($data['startTime']));
            } catch (\Exception $e) {
                return $this->json(['error' => 'startTime invalide'], 400);
            }
        }

        if (!empty($data['endTime'])) {
            try {
                $session->setEndTime(new \DateTimeImmutable($data['endTime']));
            } catch (\Exception $e) {
                return $this->json(['error' => 'endTime invalide'], 400);
            }
        }

        // Validation : fin > début
        if ($session->getStartTime() && $session->getEndTime() && $session->getEndTime() <= $session->getStartTime()) {
            return $this->json(['error' => "L'heure de fin doit être après l'heure de début."], 400);
        }

        // --------- teacherId (uniquement manager/admin) ----------
        if ($isManager && array_key_exists('teacherId', $data)) {
            if (!empty($data['teacherId'])) {
                $teacher = $teacherRepository->find((int)$data['teacherId']);
                if (!$teacher) {
                    return $this->json(['error' => 'Teacher introuvable'], 404);
                }
                $session->setTeacher($teacher);
            }
        }

        // --------- roomId (null autorisé) ----------
        if (array_key_exists('roomId', $data)) {
            if (empty($data['roomId'])) {
                $session->setRoom(null);
            } else {
                $room = $roomRepository->find((int)$data['roomId']);
                if (!$room) {
                    return $this->json(['error' => 'Room introuvable'], 404);
                }
                $session->setRoom($room);
            }
        }

        $entityManager->flush();

        // Réponse simple (tu peux enrichir si tu veux)
        return $this->json([
            'success' => true,
            'session' => [
                'id' => $session->getId(),
                'startTime' => $session->getStartTime()?->format('Y-m-d\TH:i:s'),
                'endTime' => $session->getEndTime()?->format('c'),
                'room' => $session->getRoom() ? [
                    'id' => $session->getRoom()->getId(),
                    'name' => $session->getRoom()->getName(),
                ] : null,
                'teacher' => $session->getTeacher() ? [
                    'id' => $session->getTeacher()->getId(),
                    'firstName' => $session->getTeacher()->getFirstName(),
                    'lastName' => $session->getTeacher()->getLastName(),
                ] : null,
            ],
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

    #[Route('/new-student-to-session/{id}', name: 'new_student_to_session', options: ['expose' => true], methods: ['POST'])]
    public function newStudentToClass(Request $request, Session $session): Response
    {
        $data = json_decode($request->getContent(), true);
        if (array_key_exists('studentIds', $data)) {
            foreach ($data['studentIds'] as $studentId) {
                $student = $this->studentRepository->find($studentId);
                if ($student) {
                    $sessionStudentPresence = new SessionStudyClassPresence($student, $session);
                    $this->entityManager->persist($sessionStudentPresence);
                }
            }
        }
        $this->entityManager->flush();
        return $this->apiResponse('Inscription request');
    }
}
