<?php

namespace App\Controller;

use App\Entity\Student;
use App\Entity\StudyClass;
use App\Repository\InvoiceRepository;
use App\Repository\PaymentRepository;
use App\Repository\SessionRepository;
use App\Repository\StudentClassRegisteredRepository;
use App\Repository\TeacherRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\SerializerInterface;


#[IsGranted('ROLE_USER')]
class HomeController extends AbstractController
{
    public function __construct(private EntityManagerInterface $entityManager, private UserPasswordHasherInterface $hasher)
    {
    }
    #[Route('/', name: 'app_home')]
    public function index(): Response
    {
        return $this->redirectToRoute('app_login');
    }


    #[Route('/dashboard', name: 'app_dashboard')]
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
        InvoiceRepository $invoiceRepository,
        PaymentRepository $paymentRepository,
        StudentClassRegisteredRepository $studentClassRegisteredRepository,
        SerializerInterface $serializer
    ): Response {
        // Récupération des dates (optionnelles)
        $startDateStr = $request->query->get('startDate');
        $endDateStr = $request->query->get('endDate');

        $startDate = $startDateStr ? new \DateTimeImmutable($startDateStr) : null;
        $endDate = $endDateStr ? new \DateTimeImmutable($endDateStr) : null;

        // Récupération des sessions (avec filtre ou toutes)
        $sessions = $startDate || $endDate
            ? $sessionRepository->findSessionsBetweenDates($startDate ?? new \DateTimeImmutable('2000-01-01'), $endDate ?? new \DateTimeImmutable('2100-01-01'))
            : $sessionRepository->findAll();

        $totalSessions = count($sessions);
        $totalHours = 0;
        $sessionsByTeacher = [];
        $hoursByTeacher = [];

        foreach ($sessions as $session) {
            $duration = ($session->getEndTime()->getTimestamp() - $session->getStartTime()->getTimestamp()) / 3600;
            $totalHours += $duration;
            $teacherId = $session->getTeacher()->getId();

            if (!isset($sessionsByTeacher[$teacherId])) {
                $sessionsByTeacher[$teacherId] = 0;
                $hoursByTeacher[$teacherId] = 0;
            }

            $sessionsByTeacher[$teacherId] += 1;
            $hoursByTeacher[$teacherId] += $duration;
        }

        // Récupération des enseignants
        $teachers = $teacherRepository->findAll();
        $teachersStats = array_map(fn($teacher) => [
            'id' => $teacher->getId(),
            'fullName' => $teacher->getFirstName() . ' ' . $teacher->getLastName(),
            'sessions' => $sessionsByTeacher[$teacher->getId()] ?? 0,
            'hours' => round($hoursByTeacher[$teacher->getId()] ?? 0, 2)
        ], $teachers);

        // Récupération des factures et paiements
        $invoices = $startDate || $endDate
            ? $invoiceRepository->findInvoicesBetweenDates($startDate ?? new \DateTimeImmutable('2000-01-01'), $endDate ?? new \DateTimeImmutable('2100-01-01'))
            : $invoiceRepository->findAll();

        $payments = $startDate || $endDate
            ? $paymentRepository->findPaymentsBetweenDates($startDate ?? new \DateTimeImmutable('2000-01-01'), $endDate ?? new \DateTimeImmutable('2100-01-01'))
            : $paymentRepository->findAll();

        // Calcul des montants financiers
        $totalInvoices = count($invoices);
        $totalPayments = count($payments);
        $totalInvoiceAmount = array_reduce($invoices, fn($sum, $invoice) => $sum + (float) $invoice->getTotalAmount(), 0);
        $totalPaymentAmount = array_reduce($payments, fn($sum, $payment) => $sum + (float) $payment->getAmountPaid(), 0);

        $filterSelectedYear = $request->query->get('selectedYear');
        $filterSelectedMonth = $request->query->get('selectedMonth');

// Mapping des mois en français vers des chiffres
        $monthsMapping = [
            "Janvier" => "01", "Février" => "02", "Mars" => "03", "Avril" => "04",
            "Mai" => "05", "Juin" => "06", "Juillet" => "07", "Août" => "08",
            "Septembre" => "09", "Octobre" => "10", "Novembre" => "11", "Décembre" => "12"
        ];

// Initialisation des variables
        $paymentsForUnpaidParents = [];
        $registrations = [];

        if ($filterSelectedMonth === 'all' && $filterSelectedYear === 'all') {
            // 🔹 Cas : Aucun filtre → Prend tous les paiements et inscriptions
            $paymentsForUnpaidParents = $paymentRepository->findAll();
            $registrations = $studentClassRegisteredRepository->findAll();
        } elseif ($filterSelectedMonth === 'all' && $filterSelectedYear !== 'all') {
            // 🔹 Cas : Filtre uniquement par année
            $startDate = new \DateTimeImmutable("$filterSelectedYear-01-01");
            $endDate = (new \DateTimeImmutable("$filterSelectedYear-12-01"))->modify('last day of this month');

            $paymentsForUnpaidParents = $paymentRepository->findBy(['year' => $filterSelectedYear]);
            $registrations = $studentClassRegisteredRepository->findRegisteredForUnpaidParents($endDate);
        } elseif ($filterSelectedMonth !== 'all' && $filterSelectedYear === 'all') {
            // 🔹 Cas : Filtre uniquement par mois (Toutes années confondues)
            if (!isset($monthsMapping[$filterSelectedMonth])) {
                throw new \InvalidArgumentException("Mois invalide : " . $filterSelectedMonth);
            }

            $monthNumber = $monthsMapping[$filterSelectedMonth];

            // Définit une plage temporelle large pour capturer tous les enregistrements du mois sélectionné
            $startDate = new \DateTimeImmutable("2000-$monthNumber-01");
            $endDate = (new \DateTimeImmutable("2100-$monthNumber-01"))->modify('last day of this month');

            $paymentsForUnpaidParents = $paymentRepository->findBy(['month' => $filterSelectedMonth]);
            $registrations = $studentClassRegisteredRepository->findRegisteredForUnpaidParents($endDate);
        } else {
            // 🔹 Cas : Filtre par mois ET année
            if (!isset($monthsMapping[$filterSelectedMonth])) {
                throw new \InvalidArgumentException("Mois invalide : " . $filterSelectedMonth);
            }

            $monthNumber = $monthsMapping[$filterSelectedMonth];

            // 🔹 Calculer le dernier jour du mois sélectionné dans l'année sélectionnée
            $startDate = new \DateTimeImmutable("$filterSelectedYear-$monthNumber-01");
            $endDate = $startDate->modify('last day of this month');

            $paymentsForUnpaidParents = $paymentRepository->findBy([
                'month' => $filterSelectedMonth,
                'year' => $filterSelectedYear
            ]);
            $registrations = $studentClassRegisteredRepository->findRegisteredForUnpaidParents($endDate);
        }


        // Construire un ensemble de couples (studyClass, student) pour les paiements
        $paidCouples = [];
        foreach ($paymentsForUnpaidParents as $payment) {
            $student = $payment->getStudent();
            $studyClass = $payment->getStudyClass();
            if ($student && $studyClass) {
                $paidCouples[$student->getId() . '-' . $studyClass->getId()] = true;
            }
        }
        // Identifier les inscriptions non payées
        $unpaidParents = [];
        foreach ($registrations as $registration) {
            /** @var Student $student */
            $student = $registration->getStudent();
            /** @var StudyClass $studyClass */
            $studyClass = $registration->getStudyClass();
            if ($student && $studyClass) {
                $key = $student->getId() . '-' . $studyClass->getId();
                if (!isset($paidCouples[$key])) {
                    $unpaidParents[] = [
                        'studentId' => $student->getId(),
                        'studentName' => $student->getFirstName() . ' ' . $student->getLastName(),
                        'ParentName' => $student->getParent()->getFullNameParent(),
                        'studyClassName' => $studyClass->getName() . ' ( ' . $studyClass->getSpeciality(). ' )',
                    ];
                }
            }
        }

        return $this->json([
            'totalSessions' => $totalSessions,
            'totalHours' => round($totalHours, 2),
            'teachersStats' => $teachersStats,
            'totalInvoices' => $totalInvoices,
            'totalInvoiceAmount' => round($totalInvoiceAmount, 2),
            'totalPayments' => $totalPayments,
            'totalPaymentAmount' => round($totalPaymentAmount, 2),
            'unpaidParents' => $unpaidParents,
            'allData' => [
                'invoices' => $serializer->serialize($invoices, 'json', ['groups' => 'statistic_dashboard']),
                'payments' => $serializer->serialize($payments, 'json', ['groups' => 'statistic_dashboard']),
                'sessions' => $serializer->serialize($sessions, 'json', ['groups' => 'statistic_dashboard']),
                'teachers' => $serializer->serialize($teachers, 'json', ['groups' => 'statistic_dashboard']),
                'registrations' => $serializer->serialize($registrations, 'json', ['groups' => 'read_student_class_registered']),
            ]
        ]);
    }



    #[Route('/new', name: 'app_new_page')]
    public function newPage(): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        return $this->render('home/new.html.twig', [
            'controller_path' => __FILE__,
            'template_path' => __DIR__ . '/../../templates/new_page.html.twig',
        ]);
    }
}
