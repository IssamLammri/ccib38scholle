<?php

namespace App\Command;

use App\Entity\RegistrationArabicCours;
use App\Entity\Payment;
use App\Repository\PaymentRepository;
use App\Repository\RegistrationArabicCoursRepository;
use App\Service\MailService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class PasseAllStepFromRegistersCommand extends Command
{
    protected static $defaultName = 'app:passe-all-step-from-registers';

    private EntityManagerInterface $entityManager;
    private RegistrationArabicCoursRepository $registrationArabicCoursRepository;
    private MailService $mailService;
    private PaymentRepository $paymentRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        RegistrationArabicCoursRepository $registrationArabicCoursRepository,
        MailService $mailService,
        PaymentRepository $paymentRepository
    ) {
        parent::__construct();
        $this->entityManager = $entityManager;
        $this->registrationArabicCoursRepository = $registrationArabicCoursRepository;
        $this->mailService = $mailService;
        $this->paymentRepository = $paymentRepository;
    }

    protected function configure(): void
    {
        $this            ->setName('app:passe-all-step-from-registers')
            ->setDescription('Passe toutes les inscriptions en STEP_VALIDATION si le paiement a été effectué et envoie un e-mail.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        // 1) Récupérer tous les paiements "arab"
        $allPaymentsArabicCours = $this->paymentRepository->findBy([
            'serviceType' => 'arab',
        ]);

        // 2) Construire un tableau des fullName des élèves ayant déjà payé
        $allStudentPayments = [];
        foreach ($allPaymentsArabicCours as $payment) {
            /** @var Payment $payment */
            $student = $payment->getStudent();
            if ($student) {
                $allStudentPayments[] = $student->getFullName();
            }
        }

        // 3) Récupérer toutes les inscriptions
        $allRegistered = $this->registrationArabicCoursRepository->findBy([
            'stepRegistration' => RegistrationArabicCours::STEP_PAYMENT,
        ]);

        foreach ($allRegistered as $registered) {
            /** @var RegistrationArabicCours $registered */
            $student = $registered->getStudent();
            if (!$student) {
                // pas d'élève lié → on ne traite pas
                continue;
            }

            $fullName = $student->getFullName();

            // 4) Si ce nom complet existe dans la liste des paiements → on bascule en STEP_VALIDATION
            if (in_array($fullName, $allStudentPayments, true)) {
                $registered->setStepRegistration(RegistrationArabicCours::STEP_VALIDATION);

                // 5) Envoi d'un mail de confirmation
                //    (adaptez "to:" selon l'adresse réelle du parent / de l'élève)
                $subject = 'Paiement initié – Validation en cours [' . $registered->getChildFirstName() . ' ' . $registered->getChildLastName().']';
                $this->mailService->sendEmail(
                    to: $registered->getContactEmail(), // ou ->getParents()->getEmail() selon votre entité
                    subject: $subject,
                    template: 'email/company/pass-to-validation-step-styled.html.twig',
                    context: [
                        'fullNameStudent' => $registered->getChildFirstName() . ' ' . $registered->getChildLastName(),
                        'token'           => $registered->getToken(),
                    ],
                    sender: "contact@ccib38.fr"
                );
            }
        }

        // 6) Persister toutes les modifications en base
        $this->entityManager->flush();

        $io->success('Mise à jour STEP_VALIDATION effectuée pour les inscriptions payées et e-mails envoyés.');

        return Command::SUCCESS;
    }
}
