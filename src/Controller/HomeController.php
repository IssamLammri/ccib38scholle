<?php

namespace App\Controller;

use App\Entity\StudyClass;
use App\Model\ApiResponseTrait;
use App\Repository\SessionRepository;
use App\Repository\TeacherRepository;
use App\Service\MailService;
use App\Service\SmsService;
use App\Service\UnpaidParentsService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;


#[IsGranted('ROLE_USER')]
class HomeController extends AbstractController
{
    use ApiResponseTrait;

    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserPasswordHasherInterface $hasher,
        private MailService $mailService,
        private SmsService $smsService,
    ){
    }
    #[Route('/', name: 'app_home', options: ['expose' => true])]
    public function index(): Response
    {
        return $this->redirectToRoute('app_login');
    }


    #[Route('/dashboard', name: 'app_dashboard', options: ['expose' => true])]
    public function dashboard(): Response
    {
        return $this->render('home/index.html.twig', [
            'controller_name' => 'HomeController',
        ]);
    }

    #[Route('/dashboard/api/stats', name: 'app_dashboard_stats', options: ['expose' => true], methods: ['GET'])]
    public function getStatistics(
        Request $request,
        SessionRepository $sessionRepository,
        TeacherRepository $teacherRepository,
    ): Response {
        // --- Filtres (dates par défaut = mois précédent)
        $endParam   = $request->query->get('endDate');   // "Y-m-d"
        $startParam = $request->query->get('startDate'); // "Y-m-d"

        if ($startParam && $endParam) {
            $startDate = new \DateTimeImmutable($startParam . ' 00:00:00');
            $endDate   = new \DateTimeImmutable($endParam . ' 23:59:59');
        } else {
            $today = new \DateTimeImmutable('today');
            $year  = (int)$today->format('Y');
            $month = (int)$today->format('n'); // 1..12

            $prevMonth = $month === 1 ? 12 : $month;
            $prevYear  = $month === 1 ? $year - 1 : $year;

            $startDate = (new \DateTimeImmutable(sprintf('%04d-%02d-01 00:00:00', $prevYear, $prevMonth)));
            // dernier jour du mois précédent: prendre le 1er du mois courant - 1 jour
            $endDate   = (new \DateTimeImmutable(sprintf('%04d-%02d-01 23:59:59', $year, $month)))->modify('-1 day')->setTime(23,59,59);
        }


        $classType  = $request->query->get('classType');   // Arabe | Soutien scolaire | Autre
        $speciality = $request->query->get('speciality');  // string
        $schoolYear = $request->query->get('schoolYear');  // 2024/2025 | 2025/2026
        $teacherId  = $request->query->get('teacherId');   // int

        // --- Requête sessions + joins
        $qb = $sessionRepository->createQueryBuilder('s')
            ->leftJoin('s.studyClass', 'sc')->addSelect('sc')
            ->leftJoin('s.teacher', 't')->addSelect('t')
            ->andWhere('s.startTime >= :start')
            ->andWhere('s.endTime   <= :end')
            ->setParameter('start', $startDate)
            ->setParameter('end', $endDate);

        if ($classType)  { $qb->andWhere('sc.classType = :ct')->setParameter('ct', $classType); }
        if ($speciality) { $qb->andWhere('sc.speciality = :sp')->setParameter('sp', $speciality); }
        if ($schoolYear) { $qb->andWhere('sc.schoolYear = :sy')->setParameter('sy', $schoolYear); }
        if ($teacherId)  { $qb->andWhere('t.id = :tid')->setParameter('tid', (int) $teacherId); }

        $sessions = $qb->getQuery()->getResult();

        // --- Agrégations minimales
        $totals = ['sessionsCount' => 0, 'hoursSum' => 0.0];
        $byTeacher = []; // id => { teacherId, teacherName, sessionsCount, hoursSum }
        $bySpec = [];    // pour peupler les filtres disponibles sur la plage
        $byType = [];

        foreach ($sessions as $s) {
            $start = $s->getStartTime(); $end = $s->getEndTime();
            if (!$start || !$end) { continue; }
            $hours = max(0, ($end->getTimestamp() - $start->getTimestamp()) / 3600);

            $t  = $s->getTeacher();
            $sc = $s->getStudyClass();

            $tid  = $t?->getId();
            $tname = $t ? trim(($t->getFirstName() ?? '') . ' ' . ($t->getLastName() ?? '')) : '—';

            $type = $sc?->getClassType() ?? '—';
            $spec = $sc?->getSpeciality() ?? '—';

            $totals['sessionsCount']++;
            $totals['hoursSum'] += $hours;

            if (!isset($byTeacher[$tid])) {
                $byTeacher[$tid] = ['teacherId' => $tid, 'teacherName' => $tname, 'sessionsCount' => 0, 'hoursSum' => 0.0];
            }
            $byTeacher[$tid]['sessionsCount']++;
            $byTeacher[$tid]['hoursSum'] += $hours;

            $byType[$type] = true;
            $bySpec[$spec] = true;
        }

        // Ordonne la liste des profs par heures décroissantes
        $byTeacher = array_values($byTeacher);
        usort($byTeacher, fn($a, $b) => $b['hoursSum'] <=> $a['hoursSum']);

        // --- Filtres disponibles (listes pour les <select>)
        $allTeachers = $teacherRepository->createQueryBuilder('t')
            ->orderBy('t.lastName', 'ASC')->addOrderBy('t.firstName', 'ASC')
            ->getQuery()->getResult();

        $teachersForFilter = array_map(function ($t) {
            $name = trim(($t->getFirstName() ?? '') . ' ' . ($t->getLastName() ?? ''));
            return ['id' => $t->getId(), 'name' => $name ?: '—'];
        }, $allTeachers);

        $available = [
            'classTypes'  => [StudyClass::CLASS_TYPE_ARABE, StudyClass::CLASS_TYPE_SOUTIEN, StudyClass::CLASS_TYPE_AUTRE],
            'specialities'=> array_values(array_filter(array_keys($bySpec), fn($s) => $s !== '—')),
            'teachers'    => $teachersForFilter,
            'schoolYears' => StudyClass::SCHOOL_YEARS,
        ];
        sort($available['specialities']);

        return $this->json([
            'totals'    => ['sessionsCount' => $totals['sessionsCount'], 'hoursSum' => round($totals['hoursSum'], 2)],
            'byTeacher' => $byTeacher,
            'available' => $available,
            'filtersEcho' => [
                'startDate' => $startDate->format('Y-m-d'),
                'endDate'   => $endDate->format('Y-m-d'),
                'classType' => $classType,
                'speciality'=> $speciality,
                'schoolYear'=> $schoolYear,
                'teacherId' => $teacherId ? (int)$teacherId : null,
            ],
        ]);
    }

    #[Route('/dashboard/api/unpaid-parents', name: 'app_dashboard_unpaid_parents', methods: ['GET'])]
    public function getUnpaidParents(
        Request $request,
        UnpaidParentsService $unpaidParentsService
    ): Response {
        // Année scolaire en query param, sinon valeur par défaut
        $schoolYear = $request->query->get('schoolYear');
        if (!$schoolYear) {
            $schoolYear = StudyClass::SCHOOL_YEAR_ACTIVE;
        }

        $data = $unpaidParentsService->computeUnpaidParents($schoolYear);

        return $this->json($data);
    }



    #[Route('/dashboard/api/teacher-sessions', name: 'app_dashboard_teacher_sessions', methods: ['GET'])]
    public function getTeacherSessions(Request $request, SessionRepository $sessionRepository): Response
    {
        $teacherId  = (int) $request->query->get('teacherId', 0);
        if ($teacherId <= 0) {
            return $this->json(['error' => 'teacherId manquant'], 400);
        }

        // Filtres dates
        $endParam   = $request->query->get('endDate');   // Y-m-d
        $startParam = $request->query->get('startDate'); // Y-m-d
        if ($startParam && $endParam) {
            $startDate = new \DateTimeImmutable($startParam . ' 00:00:00');
            $endDate   = new \DateTimeImmutable($endParam . ' 23:59:59');
        } else {
            // fallback: 30 derniers jours
            $endDate   = new \DateTimeImmutable('today 23:59:59');
            $startDate = $endDate->sub(new \DateInterval('P30D'))->setTime(0, 0, 0);
        }

        // Filtres optionnels
        $classType  = $request->query->get('classType');
        $speciality = $request->query->get('speciality');
        $schoolYear = $request->query->get('schoolYear');

        $qb = $sessionRepository->createQueryBuilder('s')
            ->leftJoin('s.studyClass', 'sc')->addSelect('sc')
            ->leftJoin('s.teacher', 't')->addSelect('t')
            ->andWhere('t.id = :tid')->setParameter('tid', $teacherId)
            ->andWhere('s.startTime >= :start')->setParameter('start', $startDate)
            ->andWhere('s.endTime   <= :end')->setParameter('end', $endDate)
            ->orderBy('s.startTime', 'ASC');

        if ($classType)  { $qb->andWhere('sc.classType = :ct')->setParameter('ct', $classType); }
        if ($speciality) { $qb->andWhere('sc.speciality = :sp')->setParameter('sp', $speciality); }
        if ($schoolYear) { $qb->andWhere('sc.schoolYear = :sy')->setParameter('sy', $schoolYear); }

        $sessions = $qb->getQuery()->getResult();

        $teacherName = null;
        $items = [];
        $totalHours = 0.0;

        foreach ($sessions as $s) {
            $start = $s->getStartTime(); $end = $s->getEndTime();
            if (!$start || !$end) continue;

            $h = max(0, ($end->getTimestamp() - $start->getTimestamp()) / 3600);
            $totalHours += $h;

            $t = $s->getTeacher();
            if (!$teacherName && $t) {
                $teacherName = trim(($t->getFirstName() ?? '') . ' ' . ($t->getLastName() ?? ''));
            }

            $sc = $s->getStudyClass();
            $items[] = [
                'id'        => $s->getId(),
                'startTime' => $start->format(DATE_ATOM),
                'endTime'   => $end->format(DATE_ATOM),
                'hours'     => round($h, 2),
                'studyClass'=> [
                    'name'       => $sc?->getName(),
                    'level'      => $sc?->getLevel(),
                    'speciality' => $sc?->getSpeciality(),
                    'classType'  => $sc?->getClassType(),
                    'schoolYear' => $sc?->getSchoolYear(),
                ],
            ];
        }

        return $this->json([
            'teacherId'   => $teacherId,
            'teacherName' => $teacherName,
            'total'       => ['count' => count($items), 'hours' => round($totalHours, 2)],
            'sessions'    => $items,
        ]);
    }




    #[Route('/new', name: 'app_new_page', options: ['expose' => true])]
    public function newPage(): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        return $this->render('home/new.html.twig', [
            'controller_path' => __FILE__,
            'template_path' => __DIR__ . '/../../templates/new_page.html.twig',
        ]);
    }

    #[Route('/send-email/unpaid', name: 'app_send_mail_to_unpaid_parent', options: ['expose' => true], methods: ['POST'])]
    public function sendEmailToUnpaidParent(Request $request): Response
    {
        $data = json_decode($request->getContent(), true);

        // Vérification du payload
        if (!isset($data['parents']) || !is_array($data['parents'])) {
            return $this->apiResponse('Payload invalide.', 400);
        }

        $errors = [];

        // Parcours de la liste des parents envoyés
        foreach ($data['parents'] as $parent) {
            // Vérification de l'email
            if (!isset($parent['email']) || !filter_var($parent['email'], FILTER_VALIDATE_EMAIL)) {
                $errors[] = "Email invalide pour le parent : " . ($parent['parentName'] ?? 'Inconnu');
                continue;
            }

            // Envoi de l'email
            try {
                $this->mailService->sendEmail(
                    $parent['email'],
                    'Notification de paiement impayé - Centre CCIB',
                    'email/unpaid_notification.html.twig',
                    [
                        'parentName' => $parent['parentName'],
                        'phone'      => $parent['phone']
                    ]
                );
            } catch (\Exception $e) {
                $errors[] = "Erreur lors de l'envoi de l'email à " . $parent['email'] . " : " . $e->getMessage();
            }

            // Envoi du SMS (si le numéro de téléphone est présent)
            if (!empty($parent['phone'])) {
                try {
                    $smsResponse = $this->smsService->sendSms([
                        'sender'    => 'CCIB38', // Doit respecter la limite (alphanumérique max 11 caractères)
                        'recipient' => $parent['phone'],
                        'content'   => "Bonjour,\n Vous avez un impayé concernant les frais de soutien scolaire. Veuillez vous rapprocher de notre centre pour régulariser votre situation.",
                        'tag'       => 'UnpaidNotification'
                    ]);
                    if (isset($smsResponse['error'])) {
                        $errors[] = "Erreur lors de l'envoi du SMS à " . $parent['phone'] . " : " . $smsResponse['error'];
                    }
                } catch (\Exception $e) {
                    $errors[] = "Erreur lors de l'envoi du SMS à " . $parent['phone'] . " : " . $e->getMessage();
                }
            }
        }

        if (count($errors) > 0) {
            return $this->apiResponse('Certains messages n\'ont pas pu être envoyés.', 500, $errors);
        }

        return $this->apiResponse('Emails et SMS envoyés avec succès.');
    }
}
