<?php

namespace App\Controller;

use App\Entity\Teacher;
use App\Entity\User;
use App\Form\UserEditFormType;
use App\Repository\TeacherRepository;
use App\Service\MailService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/user')]
class UserController extends AbstractController
{
    public function __construct(private TeacherRepository $teacherRepository)
    {
    }
    #[IsGranted('ROLE_ADMIN')]
    #[Route('/', name: 'user_index', options: ['expose' => true], methods: ['GET'])]
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

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/user/send-email/{id}', name: 'user_send_email', options: ['expose' => true], methods: ['GET'])]
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

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/edit/{id}', name: 'user_edit', options: ['expose' => true], methods: ['GET', 'POST'])]
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

    #[Route('/verify-account', name: 'verify_account', options: ['expose' => true], methods: ['GET'])]
    public function verifyAccount(Request $request, EntityManagerInterface $entityManager): Response
    {
        $token = $request->query->get('token');

        if (!$token) {
            throw $this->createNotFoundException('Token non fourni.');
        }

        $user = $entityManager->getRepository(User::class)->findOneBy(['verificationToken' => $token]);

        if (!$user || $user->getTokenExpiration() < new \DateTimeImmutable()) {
            $this->addFlash('danger', 'Votre lien de réinitialisation est invalide ou a expiré. Veuillez en demander un nouveau.');
            return $this->redirectToRoute('app_login');
        }

        // Supprimer le token pour qu'il ne puisse pas être réutilisé directement
        $user->setVerificationToken($token); // Conserve temporairement pour vérification
        $entityManager->flush();

        return $this->redirectToRoute('reset_password', ['id' => $user->getId(), 'token' => $token]);
    }

    #[Route('/reset-password/{token}', name: 'reset_password', options: ['expose' => true], methods: ['GET', 'POST'])]
    public function resetPassword(string $token, Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): Response
    {
        $user = $entityManager->getRepository(User::class)->findOneBy(['verificationToken' => $token]);

        if (!$user || $user->getTokenExpiration() < new \DateTimeImmutable()) {
            $this->addFlash('danger', 'Votre lien de réinitialisation est invalide ou a expiré. Veuillez en demander un nouveau.');
            return $this->redirectToRoute('forgot_password');
        }

        if ($request->isMethod('POST')) {
            $newPassword = $request->request->get('password');
            $confirmPassword = $request->request->get('confirm_password');

            if ($newPassword !== $confirmPassword) {
                $this->addFlash('danger', 'Les mots de passe ne correspondent pas.');
            } elseif (strlen($newPassword) < 8) {
                $this->addFlash('danger', 'Le mot de passe doit contenir au moins 8 caractères.');
            } else {
                $user->setPassword($passwordHasher->hashPassword($user, $newPassword));
                $user->setVerificationToken(null);
                $user->setTokenExpiration(null);
                $entityManager->flush();

                $this->addFlash('success', 'Votre mot de passe a été enregistré avec succès. Vous pouvez maintenant vous connecter avec.');
                return $this->redirectToRoute('app_login');
            }
        }

        $isPasswordInitialization = !$user->isVerified();

        return $this->render('security/reset_password.html.twig', [
            'isPasswordInitialization' => $isPasswordInitialization,
        ]);
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/delete/{id}', name: 'user_delete', options: ['expose' => true], methods: ['POST'])]
    public function delete(Request $request, User $user, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$user->getId(), $request->request->get('_token'))) {
            $teacher = $this->teacherRepository->findOneBy(['user' => $user]);
            if ($teacher) {
                $teacher->setUser(null);
            }
            $entityManager->remove($user);
            $entityManager->flush();

            $this->addFlash('success', 'User deleted successfully!');
        }

        return $this->redirectToRoute('user_index');
    }

    #[Route('/forgot-password', name: 'forgot_password', options: ['expose' => true], methods: ['GET', 'POST'])]
    public function forgotPassword(
        Request $request,
        EntityManagerInterface $entityManager,
        MailService $mailService
    ): Response {
        if ($request->isMethod('POST')) {
            $email = $request->request->get('email');

            $user = $entityManager->getRepository(User::class)->findOneBy(['email' => $email]);
            if ($user) {
                $token = bin2hex(random_bytes(32));
                $user->setVerificationToken($token);
                $user->setTokenExpiration(new \DateTimeImmutable('+1 hour'));
                $entityManager->flush();

                $url = $this->generateUrl('reset_password', ['token' => $token], UrlGeneratorInterface::ABSOLUTE_URL);
                $context = [
                    'user' => $user,
                    'url' => $url,
                    'firstName' => $user->getFirstName(),
                    'lastName' => $user->getLastName(),
                ];
                try {
                    $mailService->sendEmail(
                        $user->getEmail(),
                        'Réinitialisation de votre mot de passe - École CCIB 38',
                        'email/reset_password.html.twig',
                        $context
                    );
                    $this->addFlash('success', 'Un email de réinitialisation a été envoyé. Veuillez vérifier votre boîte mail pour réinitialiser votre mot de passe si vous avez un compte chez nous.');
                } catch (\Exception $e) {
                    $this->addFlash('danger', 'Une erreur est survenue lors de l\'envoi de l\'email : ' . $e->getMessage());
                }
            } else {
                $this->addFlash('danger', 'Aucun compte trouvé pour cet email.');
            }
        }

        return $this->render('security/forgot_password.html.twig');
    }
}
