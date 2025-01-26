<?php

namespace App\Controller;

use App\Repository\SessionRepository;
use App\Repository\TeacherRepository;
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
    public function getStatistics(Request $request, SessionRepository $sessionRepository, TeacherRepository $teacherRepository): Response
    {
        $startDate = $request->query->get('startDate');
        $endDate = $request->query->get('endDate');

        $queryBuilder = $sessionRepository->createQueryBuilder('s');

        if ($startDate) {
            $queryBuilder->andWhere('s.startTime >= :startDate')
                ->setParameter('startDate', new \DateTime($startDate));
        }

        if ($endDate) {
            $queryBuilder->andWhere('s.endTime <= :endDate')
                ->setParameter('endDate', new \DateTime($endDate));
        }

        $sessions = $queryBuilder->getQuery()->getResult();

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

        $teachers = $teacherRepository->findAll();
        $teachersStats = array_map(fn($teacher) => [
            'id' => $teacher->getId(),
            'fullName' => $teacher->getFirstName() . ' ' . $teacher->getLastName(),
            'sessions' => $sessionsByTeacher[$teacher->getId()] ?? 0,
            'hours' => round($hoursByTeacher[$teacher->getId()] ?? 0, 2)
        ], $teachers);

        return $this->json([
            'totalSessions' => $totalSessions,
            'totalHours' => round($totalHours, 2),
            'teachersStats' => $teachersStats
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
