<?php

namespace App\Controller;

use App\Model\ApiResponseTrait;
use App\Repository\AcademicSupportRegistrationRepository;
use App\Service\AcademicSupportRegistrationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/soutien-scolaire')]
class academicSupportController extends AbstractController
{
    use ApiResponseTrait;

    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserPasswordHasherInterface $hasher,
        private AcademicSupportRegistrationRepository $academicSupportRegistrationRepository
    )
    {
    }
    #[Route('/inscreption', name: 'app_registration_academic_support', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('academic_support/new.html.twig', [
            'controller_path' => __FILE__,
            'template_path' => __DIR__ . '/../../templates/new_page.html.twig',
        ]);
    }

    #[Route('/inscription-requests', name: 'app_registration_academic_support_request', options: ['expose' => true], methods: ['POST'])]
    public function inscriptionRequest(Request $request, AcademicSupportRegistrationService $service): JsonResponse
    {
        $data = json_decode($request->getContent(), true) ?? [];

        try {
            $reg = $service->createFromPayload($data);

            return $this->json([
                'success' => true,
                'id'      => $reg->getId(),
                'token'   => $reg->getPublicToken(), // utile pour un lien /inscription-show/{token}
                'status'  => $reg->getStatus(),
            ], 201);

        } catch (\InvalidArgumentException $e) {
            // Erreurs de validation (renvoie le tableau sérialisé en JSON)
            return $this->json([
                'success' => false,
                'errors'  => json_decode($e->getMessage(), true),
            ], 422);

        } catch (\Throwable $e) {
            // Log si besoin
            return $this->json([
                'success' => false,
                'error'   => 'SERVER_ERROR',
            ], 500);
        }
    }

    #[Route('/registers', name: 'app_registers_academic_support', options: ['expose' => true], methods: ['GET'])]
    public function registers(): Response
    {
        return $this->render('registration_academic_support/list.html.twig', [
            'controller_name' => 'registers academic support',
        ]);
    }

    #[Route('/registers/api/list', name: 'app_registers_academic_support_list', options: ['expose' => true], methods: ['GET'])]
    public function getRegisters(
        Request $request,
    ): Response {
        $registrations = $this->academicSupportRegistrationRepository->findAll();

        // on renvoie directement le tableau, en précisant le groupe de sérialisation
        return $this->json(
            [
                'status'    => 'success',
                'registrations' => $registrations,  // PAS de serialize() ici
            ],
            Response::HTTP_OK,
            [],
            ['groups' => ['read_list_registration_academic_support']]
        );
    }
}
