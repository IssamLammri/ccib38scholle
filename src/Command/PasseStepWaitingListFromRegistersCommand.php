<?php
// src/Command/PasseStepWaitingListFromRegistersCommand.php

namespace App\Command;

use App\Entity\RegistrationArabicCours;
use App\Repository\RegistrationArabicCoursRepository;
use App\Service\RegistrationService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class PasseStepWaitingListFromRegistersCommand extends Command
{
    protected static $defaultName = 'app:step-from-registers-update-status';
    protected static $defaultDescription = 'Passe toutes les inscriptions en liste d’attente à l’étape de paiement et envoie les e-mails associés.';

    private RegistrationArabicCoursRepository $registrationRepo;
    private RegistrationService $registrationService;

    public function __construct(
        RegistrationArabicCoursRepository $registrationRepo,
        RegistrationService $registrationService
    ) {
        parent::__construct();
        $this->registrationRepo     = $registrationRepo;
        $this->registrationService = $registrationService;
    }

    protected function configure(): void
    {
        // Le defaultName et defaultDescription sont déjà définis en propriétés
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        // Récupère toutes les inscriptions en "waiting"
        $waitingList = $this->registrationRepo->findBy([
            'stepRegistration' => RegistrationArabicCours::STEP_LIST_WAITING,
        ]);

        $count = 0;
        foreach ($waitingList as $registration) {
            try {
                // AdvanceStep gère la mise à jour + envoi du mail "waiting → payment"
                $this->registrationService->advanceStep($registration);
                $count++;
            } catch (\DomainException $e) {
                $io->warning(sprintf(
                    'Inscription #%d non traitée : %s',
                    $registration->getId(),
                    $e->getMessage()
                ));
            }
        }

        $io->success(sprintf(
            '%d inscription(s) passées à l’étape Paiement et e-mails envoyés.',
            $count
        ));

        return Command::SUCCESS;
    }
}
