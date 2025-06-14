<?php

namespace App\Command;

use App\Entity\ParentEntity;
use App\Entity\RegistrationArabicCours;
use App\Entity\Student;
use App\Repository\ParentsRepository;
use App\Repository\RegistrationArabicCoursRepository;
use App\Repository\StudentRepository;
use App\Service\MailService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class PasseStepWaitingListFromRegistersCommand extends Command
{
    protected static $defaultName = 'app:passe-step'; // Définit un nom valide pour la commande

    private EntityManagerInterface $entityManager;
    private RegistrationArabicCoursRepository $registrationArabicCoursRepository;

    private ParentsRepository $parentsRepository;

    private StudentRepository $studentRepository;
    private MailService      $mailService;

    public function __construct(
        EntityManagerInterface $entityManager,
        RegistrationArabicCoursRepository $registrationArabicCoursRepository,
        ParentsRepository $parentsRepository,
        StudentRepository $studentRepository,
        MailService $mailService
    ){
        parent::__construct();
        $this->entityManager = $entityManager;
        $this->registrationArabicCoursRepository = $registrationArabicCoursRepository;
        $this->parentsRepository = $parentsRepository;
        $this->studentRepository = $studentRepository;
        $this->mailService = $mailService;
    }

    protected function configure(): void
    {
        $this
            ->setName('app:step-from-registers-update-status')
            ->setDescription('Create sessions based on provided parameters and data.');
    }


    /**
     * @throws \DateMalformedStringException
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $allRegistered = $this->registrationArabicCoursRepository->findBy(['stepRegistration' => RegistrationArabicCours::STEP_LIST_WAITING]);
        foreach ($allRegistered as $registration) {
            /** @var RegistrationArabicCours $registration */
            $registration->setStepRegistration(RegistrationArabicCours::STEP_PAYMENT);
            $subject = 'Candidature approuvée pour 2025/2026 ['.$registration->getChildFirstName(). ' ' . $registration->getChildLastName().'] &  règlement des frais requis';
            $this->mailService->sendEmail(
                to: $registration->getContactEmail(),
                subject: $subject,
                template: 'email/company/pass-to-payment-step.html.twig',
                context: [
                    'fullNameStudent' => $registration->getChildFirstName(). ' ' . $registration->getChildLastName(),
                    'token'=> $registration->getToken(),
                ],
                sender: "contact@ccib38.fr"
            );

        }
        $this->entityManager->flush();

        $io->success('Les inscriptions ont été mises à jour avec succès et les e-mails ont été envoyés.');

        return Command::SUCCESS;
    }
}
