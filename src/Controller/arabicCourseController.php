<?php

namespace App\Controller;

use App\Entity\ParentEntity;
use App\Entity\RegistrationArabicCours;
use App\Entity\Student;
use App\Model\ApiResponseTrait;
use App\Repository\ParentsRepository;
use App\Repository\RegistrationArabicCoursRepository;
use App\Repository\StudentRepository;
use App\Service\MailService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/cours-arabe')]
class arabicCourseController extends AbstractController
{
    use ApiResponseTrait;

    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserPasswordHasherInterface $hasher,
        private MailService $mailService,
        private ParentsRepository $parentsRepository,
        private StudentRepository $studentRepository,
    )
    {
    }
    #[Route('/inscription', name: 'app_registration_course_arabe', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('cours_arabe/new.html.twig', [
            'controller_path' => __FILE__,
            'template_path' => __DIR__ . '/../../templates/new_page.html.twig',
        ]);
    }

    #[Route('/inscription-requests', name: 'app_registration_arabic_course_request', options: ['expose' => true], methods: ['POST'])]
    public function inscriptionRequest(
        Request $request,
        EntityManagerInterface $em,
        SluggerInterface $slugger
    ): Response {
        // 1) lecture des champs texte
        $data = $request->request->all();

        // traitement du champ authorized
        $rawAuthorized = $data['authorized'] ?? '[]';
        $authorized = json_decode($rawAuthorized, true);
        if (!is_array($authorized)) {
            $authorized = [];
        }

        // 2) création de l’entité
        $registration = new RegistrationArabicCours();
        $registration
            ->setChildFirstName($data['childFirstName'])
            ->setChildLastName($data['childLastName'])
            ->setChildDob(\DateTimeImmutable::createFromFormat('d/m/Y', $data['childDob']))
            ->setChildGender($data['childGender'])
            ->setChildLevel($data['childLevel'])
            ->setFatherFirstName($data['fatherFirstName'])
            ->setFatherLastName($data['fatherLastName'])
            ->setMotherFirstName($data['motherFirstName'])
            ->setMotherLastName($data['motherLastName'])
            ->setContactEmail($data['contactEmail'])
            ->setFatherPhone($data['fatherPhone'])
            ->setMotherPhone($data['motherPhone'])
            ->setAddress($data['address'])
            ->setPostalCode($data['postalCode'])
            ->setCity($data['city'])
            ->setAuthorized($authorized)
            ->setAuthorizedOtherName($data['authorizedOtherName'] ?? null)
            ->setAuthorizedOtherRelation($data['authorizedOtherRelation'] ?? null)
            ->setLeaveAlone($data['leaveAlone'])
            ->setImageRights($data['imageRights'])
            ->setCommitmentSitu((bool) $data['commitmentSitu'])
            ->setMedicalInfo($data['medicalInfo'])
            ->setMedicalDetails($data['medicalDetails'] ?? null)
            ->setMedicalTreatment($data['medicalTreatment'])
            ->setLegalDeclaration((bool) $data['legalDeclaration'])
            ->setPaymentTerms((bool) $data['paymentTerms'])
            ->setWasEnrolled2024($data['wasEnrolled2024'] ?? null)
            ->setPreviousLevel($data['previousLevel'] ?? null)
            ->setSiblingEnrolled($data['siblingEnrolled'] ?? null);

        if ($data['wasEnrolled2024'] === 'oui') {
            $registration->setStepRegistration(RegistrationArabicCours::STEP_PAYMENT);
        } else {
            $registration->setStepRegistration(RegistrationArabicCours::STEP_LIST_WAITING);
        }
        // 3) upload de la photo si présente
        /** @var UploadedFile|null $photoFile */
        $photoFile = $request->files->get('childPhoto');
        if ($photoFile) {
            $originalFilename = pathinfo($photoFile->getClientOriginalName(), PATHINFO_FILENAME);
            $safeFilename     = $slugger->slug($originalFilename);
            $newFilename      = sprintf('%s-%s.%s', $safeFilename, uniqid(), $photoFile->guessExtension());
            $photoFile->move(
                $this->getParameter('uploads_directory'),
                $newFilename
            );
            $registration->setChildPhotoFilename($newFilename);
        }

        // 4) persistance
        $em->persist($registration);
        $em->flush();

        // 5) envoi de l’e-mail de confirmation
        $this->mailService->sendEmail(
            $registration->getContactEmail(),
            'Confirmation de votre demande d\'inscription [Cours d\'arabe] – CCIB 38',
            'email/registration_confirmation.html.twig',
            [
                'firstName'         => $registration->getChildFirstName(),
                'lastName'          => $registration->getChildLastName(),
                'childDob'          => $registration->getChildDob(),
                'childLevel'        => $registration->getChildLevel(),
                'wasEnrolled2024'   => $registration->getWasEnrolled2024(),
                'token'             => $registration->getToken(),
            ]
        );

        // 6) Création de parent et des enfants
        $parentFather = $this->parentsRepository->findOneBy(['fatherEmail' => $registration->getContactEmail()]);
        if ($parentFather === null){
            $parentMather = $this->parentsRepository->findOneBy(['motherEmail' => $registration->getContactEmail()]);
            if ($parentMather === null){
                $parent = new ParentEntity();
                $parent
                    ->setFatherFirstName($registration->getFatherFirstName())
                    ->setFatherLastName($registration->getFatherLastName())
                    ->setFatherEmail($registration->getContactEmail())
                    ->setFatherPhone($registration->getFatherPhone())
                    ->setMotherFirstName($registration->getMotherFirstName())
                    ->setMotherLastName($registration->getMotherLastName())
                    ->setMotherEmail($registration->getContactEmail())
                    ->setMotherPhone($registration->getMotherPhone())
                    ->setFamilyStatus('married');
                $this->entityManager->persist($parent);
                $this->entityManager->flush();
            } else {
                $parent = $parentMather;
            }
        } else {
            $parent = $parentFather;
        }

        // Enregistrement de l'enfant
        $student = $this->studentRepository->findOneByNameAndParent(
            $registration->getChildFirstName(),
            $registration->getChildLastName(),
            $parent
        );
        if ($student === null){
            $student = new Student();
            $student
                ->setFirstName($registration->getChildFirstName())
                ->setLastName($registration->getChildLastName())
                ->setBirthDate($registration->getChildDob())
                ->setParent($parent)
                ->setGender($registration->getChildGender())
                ->setCity($registration->getCity())
                ->setAddress($registration->getAddress())
                ->setPostalCode($registration->getPostalCode())
                ->setLevel($registration->getChildLevel());
            $this->entityManager->persist($student);
            $this->entityManager->flush();
        }

        $student->addRegistrationArabicCours($registration);
        $this->entityManager->flush();
        // 7) réponse JSON avec le token
        return $this->json([
            'status' => 'success',
            'token'  => $registration->getToken(),
        ], Response::HTTP_CREATED);
    }

    #[Route('/inscription-show/{token}', name: 'app_registration_arabic_course_view',options: ['expose'=>true], methods:['GET'])]
    public function viewRequest(string $token, RegistrationArabicCoursRepository $repo): Response
    {
        $registration = $repo->findOneBy(['token' => $token]);

        if (!$registration) {
            throw $this->createNotFoundException('Demande introuvable');
        }

        return $this->render('cours_arabe/show.html.twig', [
            'registration' => $registration,
        ]);
    }

}
