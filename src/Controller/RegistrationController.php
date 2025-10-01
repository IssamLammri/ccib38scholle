<?php

namespace App\Controller;

use App\Entity\RegistrationArabicCours;
use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Model\ApiResponseTrait;
use App\Repository\RegistrationArabicCoursRepository;
use App\Security\EmailVerifier;
use App\Service\RegistrationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;
#[IsGranted('ROLE_MANAGER')]
class RegistrationController extends AbstractController
{
    use ApiResponseTrait;
    public function __construct(
        private EmailVerifier $emailVerifier,
        private  SerializerInterface $serializer,
        private RegistrationArabicCoursRepository $arabicCoursRepository,
        private RegistrationService $registrationService
    ){
    }

    #[Route('/register', name: 'app_register', options: ['expose' => true])]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, Security $security, EntityManagerInterface $entityManager): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Encode le mot de passe
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );

            // Récupérer les rôles depuis le formulaire
            $roles = $form->get('roles')->getData();
            $user->setRoles($roles);

            $entityManager->persist($user);
            $entityManager->flush();

            // Redirection après la création
            $this->addFlash('success', 'User successfully created!');
            return $this->redirectToRoute('user_index'); // Ou une autre route pertinente
        }


        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form,
        ]);
    }

    #[Route('/verify/email', name: 'app_verify_email', options: ['expose' => true])]
    public function verifyUserEmail(Request $request, TranslatorInterface $translator): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        // validate email confirmation link, sets User::isVerified=true and persists
        try {
            $this->emailVerifier->handleEmailConfirmation($request, $this->getUser());
        } catch (VerifyEmailExceptionInterface $exception) {
            $this->addFlash('verify_email_error', $translator->trans($exception->getReason(), [], 'VerifyEmailBundle'));

            return $this->redirectToRoute('app_register');
        }

        // @TODO Change the redirect on success and handle or remove the flash message in your templates
        $this->addFlash('success', 'Your email address has been verified.');

        return $this->redirectToRoute('app_register');
    }

    #[Route('/registers', name: 'app_registers', options: ['expose' => true])]
    public function registers(): Response
    {
        return $this->render('registration/list.html.twig', [
            'controller_name' => 'registers',
        ]);
    }

    #[Route('/registers/api/list', name: 'app_registers_list', options: ['expose' => true], methods: ['GET'])]
    public function getRegisters(
        Request $request,
    ): Response {
        $registrations = $this->arabicCoursRepository->findAll();

        // on renvoie directement le tableau, en précisant le groupe de sérialisation
        return $this->json(
            [
                'status'    => 'success',
                'registers' => $registrations,  // PAS de serialize() ici
            ],
            Response::HTTP_OK,
            [],
            ['groups' => ['read_list_registration']]
        );
    }


    #[Route('/registers/{id}/advance', name: 'app_registration_advance', options: ['expose' => true], methods: ['POST'])]
    public function advance(RegistrationArabicCours $registration): Response
    {
        try {
            $newStep = $this->registrationService->advanceStep($registration);
            return $this->json([
                'status'  => 'success',
                'newStep' => $newStep
            ]);
        } catch (\DomainException $e) {
            return $this->json(
                ['status' => 'error', 'message' => $e->getMessage()],
                Response::HTTP_BAD_REQUEST
            );
        }
    }
}
