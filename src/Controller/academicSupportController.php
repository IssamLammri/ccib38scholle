<?php

namespace App\Controller;

use App\Model\ApiResponseTrait;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/soutien-scolaire')]
class academicSupportController extends AbstractController
{
    use ApiResponseTrait;

    public function __construct(private EntityManagerInterface $entityManager, private UserPasswordHasherInterface $hasher)
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
    public function inscriptionRequest(Request $request): Response
    {
        $data = json_decode($request->getContent(), true);

        return $this->apiResponse('Inscription request');
    }
}
