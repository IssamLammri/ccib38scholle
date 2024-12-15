<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserEditFormType;
use App\Service\MailService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/user')]
class UserController extends AbstractController
{
    #[Route('/', name: 'user_index', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager, Request $request): Response
    {
        $page = $request->query->getInt('page', 1); // Page actuelle, par défaut 1
        $limit = 10; // Nombre de résultats par page
        $search = $request->query->get('search', ''); // Filtre de recherche

        // Construction de la requête pour récupérer les utilisateurs
        $queryBuilder = $entityManager->getRepository(User::class)
            ->createQueryBuilder('u');

        // Ajout du filtre de recherche sur les colonnes nécessaires
        if (!empty($search)) {
            $queryBuilder
                ->where('u.firstName LIKE :search')
                ->orWhere('u.lastName LIKE :search')
                ->orWhere('u.email LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }

        // Pagination : définir l'offset et la limite
        $queryBuilder
            ->setFirstResult(($page - 1) * $limit) // Calcul de l'offset
            ->setMaxResults($limit); // Limite des résultats

        $paginator = new Paginator($queryBuilder); // Création du Paginator

        return $this->render('user/index.html.twig', [
            'users' => $paginator,
            'current_page' => $page,
            'total_pages' => ceil(count($paginator) / $limit), // Calcul du total de pages
            'search' => $search,
        ]);
    }

    #[Route('/user/send-email/{id}', name: 'user_send_email', methods: ['GET'])]
    public function sendEmail(int $id, EntityManagerInterface $entityManager, MailService $mailService): Response
    {
        $user = $entityManager->getRepository(User::class)->find($id);

        if (!$user) {
            $this->addFlash('error', 'Utilisateur introuvable.');
            return $this->redirectToRoute('user_index');
        }

        try {
            $mailService->sendEmail(
                $user->getEmail(),
                'Test Bienvenue sur notre plateforme',
                'email/welcome.html.twig',
                [
                    'firstName' => $user->getFirstName(),
                    'lastName' => $user->getLastName(),
                    'email' => $user->getEmail(),
                ]
            );
            $this->addFlash('success', 'Email envoyé avec succès à ' . $user->getEmail());
        } catch (\Exception $e) {
            $this->addFlash('error', 'Une erreur est survenue lors de l\'envoi de l\'email : ' . $e->getMessage());
        }

        return $this->redirectToRoute('user_index');
    }

    #[Route('/edit/{id}', name: 'user_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, User $user, UserPasswordHasherInterface $passwordHashed, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(UserEditFormType::class, $user);
        $form->handleRequest($request);
        if (!$this->isGranted('ROLE_ADMIN')) {
            $form->remove('roles');
        }
        if ($form->isSubmitted() && $form->isValid()) {
            // Gestion du changement de mot de passe
            $oldPassword = $form->get('oldPassword')->getData();
            $newPassword = $form->get('newPassword')->getData();

            if ($newPassword) {
                // Vérifie si l'ancien mot de passe est correct
                if ($passwordHashed->isPasswordValid($user, $oldPassword)) {
                    $user->setPassword($passwordHashed->hashPassword($user, $newPassword));
                } else {
                    $this->addFlash('error', 'Old password is incorrect.');
                    return $this->redirectToRoute('user_edit', ['id' => $user->getId()]);
                }
            }

            $entityManager->flush();
            $this->addFlash('success', 'User updated successfully!');
            return $this->redirectToRoute('user_index');
        }

        return $this->render('user/edit.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/delete/{id}', name: 'user_delete', methods: ['POST'])]
    public function delete(Request $request, User $user, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$user->getId(), $request->request->get('_token'))) {
            $entityManager->remove($user);
            $entityManager->flush();

            $this->addFlash('success', 'User deleted successfully!');
        }

        return $this->redirectToRoute('user_index');
    }
}
