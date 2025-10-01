<?php

namespace App\Controller;

use App\Model\ApiResponseTrait;
use App\Repository\InvoiceRepository;
use App\Repository\ParentsRepository;
use App\Repository\PaymentRepository;
use App\Repository\SessionRepository;
use App\Repository\StudentClassRegisteredRepository;
use App\Repository\TeacherRepository;
use App\Service\MailService;
use App\Service\ParentDashboardService;
use App\Service\SmsService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\SerializerInterface;


#[IsGranted('ROLE_USER')]
#[Route('/espace-parent')]
class DashboardParentController extends AbstractController
{
    use ApiResponseTrait;

    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserPasswordHasherInterface $hasher,
        private MailService $mailService,
        private SmsService $smsService,
        private ParentsRepository $parentRepo,
        private ParentDashboardService $dashboardService
    ){
    }

    #[Route('/dashboard', name: 'app_dashboard_parent', options: ['expose' => true] )]
    public function dashboard(): Response
    {
        return $this->render('parentsAccess/dashboard.html.twig', [
            'controller_name' => 'HomeController',
        ]);
    }


    #[Route('/dashboard/api/parent', name: 'app_dashboard_data_parent', options: ['expose' => true], methods: ['GET'])]
    public function getDataForDashboardParent(
        Request $request,
    ): Response {
        $user = $this->getUser();
        if (!$user) {
            return $this->apiErrorResponse('Utilisateur non authentifié', Response::HTTP_UNAUTHORIZED);
        }

        $parent = $this->parentRepo->findOneBy(['user' => $user]);
        if (!$parent) {
            return $this->apiErrorResponse('Utilisateur non autorisé ou sans parent associé', Response::HTTP_FORBIDDEN);
        }

        // Optionnel : plage passée en query ?start=2025-09-15&end=2025-09-21
        $start = $request->query->get('start') ? new \DateTimeImmutable($request->query->get('start').' 00:00:00') : null;
        $end   = $request->query->get('end')   ? new \DateTimeImmutable($request->query->get('end').' 23:59:59') : null;

        $payload = $this->dashboardService->buildDashboardPayload($parent, $start, $end);

        // IMPORTANT : ton ApiResponseTrait place le tableau dans "message"
        // donc côté front, lis bien response.data.message
        return $this->apiResponse($payload);
    }
}
