<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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


    #[Route('/home', name: 'app_first_page')]
    public function firstPage(): Response
    {
        return $this->render('home/index.html.twig', [
            'controller_name' => 'HomeController',
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
