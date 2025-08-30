<?php
// src/Command/PasseAllStepFromRegistersCommand.php

namespace App\Command;

use App\Entity\RegistrationArabicCours;
use App\Repository\PaymentRepository;
use App\Repository\RegistrationArabicCoursRepository;
use App\Service\RegistrationService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class PasseAllStepFromRegistersCommand extends Command
{
    protected static $defaultName = 'app:passe-all-step-from-registers';

    private RegistrationService $registrationService;
    private PaymentRepository $paymentRepository;
    private RegistrationArabicCoursRepository $registrationRepo;

    public function __construct(
        RegistrationService $registrationService,
        PaymentRepository $paymentRepository,
        RegistrationArabicCoursRepository $registrationRepo
    ) {
        parent::__construct(self::$defaultName);
        $this->registrationService = $registrationService;
        $this->paymentRepository   = $paymentRepository;
        $this->registrationRepo    = $registrationRepo;
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Passe toutes les inscriptions éligibles de STEP_PAYMENT à STEP_VALIDATION et envoie les e-mails correspondants.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        // 1) Récupérer tous les paiements pour le service "arabe"
        $payments = $this->paymentRepository->findBy(['serviceType' => 'arabe']);

        // 2) Calculer le total payé par chaque parent
        $totauxParParent = [];
        foreach ($payments as $p) {
            if (null === $p->getParent()) {
                continue;
            }
            $pid = $p->getParent()->getId();
            $totauxParParent[$pid] = ($totauxParParent[$pid] ?? 0.0) + (float) $p->getAmountPaid();
        }

        // 3) Lister les IDs des étudiants dont le parent a payé > 230€
        $etudiantsEligibles = [];
        foreach ($payments as $p) {
            $parent = $p->getParent();
            $student = $p->getStudent();
            if (
                $parent
                && $student
                && ($totauxParParent[$parent->getId()] ?? 0.0) > 10.0
            ) {
                $etudiantsEligibles[$student->getId()] = true;
            }
        }

        // 4) Récupérer toutes les inscriptions en STEP_PAYMENT
        $toProcess = $this->registrationRepo->findBy([
            'stepRegistration' => RegistrationArabicCours::STEP_PAYMENT,
        ]);

        $count = 0;
        foreach ($toProcess as $reg) {
            /** @var RegistrationArabicCours $reg */
            $student = $reg->getStudent();
            if ($student && isset($etudiantsEligibles[$student->getId()])) {
                try {
                    // Appel unique : avance l'étape et envoie l’e-mail si nécessaire
                    $this->registrationService->advanceStep($reg);
                    $count++;
                } catch (\DomainException $e) {
                    // par sécurité on continue sur erreur métier
                    $io->warning("Inscription #{$reg->getId()} non avancée : {$e->getMessage()}");
                }
            }
        }

        $io->success("$count inscription(s) passées en validation et e-mails envoyés.");
        return Command::SUCCESS;
    }
}
