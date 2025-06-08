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

        // 1) Récupérer tous les paiements « arab »
        $paymentsArabic = $this->paymentRepository->findBy([
            'serviceType' => 'arabe',
        ]);

        // 2) Calculer le total payé par chaque parent
        $parentTotals = [];
        foreach ($paymentsArabic as $payment) {
            /** @var Payment $payment */
            $parent = $payment->getParent();
            if (null === $parent) {
                continue;
            }

            $pid = $parent->getId();
            $parentTotals[$pid] = ($parentTotals[$pid] ?? 0.0) + (float) $payment->getAmountPaid();
        }

        // 3) Construire la liste des noms complets des étudiants éligibles
        $eligibleStudentNames = [];
        foreach ($paymentsArabic as $payment) {
            /** @var Payment $payment */
            $student = $payment->getStudent();
            $parent  = $payment->getParent();

            if (null === $student || null === $parent) {
                continue;
            }

            $pid = $parent->getId();
            if (($parentTotals[$pid] ?? 0.0) > 230.0) {
                $eligibleStudentNames[$student->getId()] = $student->getFullName();
            }
        }

        // on ne garde que les valeurs (noms complets), sans doublons
        $allStudentPayments = array_values($eligibleStudentNames);
        // 4) Récupérer toutes les inscriptions en attente de paiement
        $allRegistered = $this->registrationArabicCoursRepository->findBy([
            'stepRegistration' => RegistrationArabicCours::STEP_PAYMENT,
        ]);

        foreach ($allRegistered as $registered) {
            /** @var RegistrationArabicCours $registered */
            $student = $registered->getStudent();
            if (null === $student) {
                // pas d’élève lié → on ignore
                continue;
            }


            $fullName = $student->getFullName();
            // 5) si l’élève a payé via son parent plus de 230 €, on passe en validation
            if (in_array($fullName, $allStudentPayments, true)) {
                $registered->setStepRegistration(RegistrationArabicCours::STEP_VALIDATION);

                // 6) envoi d’un mail de confirmation
                $subject = sprintf(
                    'Paiement initié – Validation en cours [%s %s]',
                    $registered->getChildFirstName(),
                    $registered->getChildLastName()
                );

                 $this->mailService->sendEmail(
                     to: $registered->getContactEmail(),
                     subject: $subject,
                     template: 'email/company/pass-to-validation-step-styled.html.twig',
                     context: [
                         'fullNameStudent' => $fullName,
                         'token'           => $registered->getToken(),
                     ],
                     sender: 'contact@ccib38.fr'
                 );
            }
        }

// 7) persister toutes les modifications
        $this->entityManager->flush();

        $io->success('Mise à jour STEP_VALIDATION effectuée et e-mails envoyés.');

        return Command::SUCCESS;
    }
}
