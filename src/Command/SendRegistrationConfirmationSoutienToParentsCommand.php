<?php

namespace App\Command;

use App\Entity\StudentClassRegistered;
use App\Entity\StudyClass;
use App\Repository\StudentClassRegisteredRepository;
use App\Repository\StudyClassRepository;
use App\Service\MailService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class SendRegistrationConfirmationSoutienToParentsCommand extends Command
{
    protected static $defaultName = 'app:send-email-company-haj';

    private EntityManagerInterface $entityManager;
    private MailService $mailService;

    public function __construct(
        EntityManagerInterface $entityManager,
        MailService $mailService,
        private StudyClassRepository $studyClassRepository,
        private StudentClassRegisteredRepository $studentClassRegisteredRepository

    )
    {
        parent::__construct();
        $this->entityManager = $entityManager;
        $this->mailService = $mailService;
    }

    protected function configure(): void
    {
        $this
                ->setName('app:send-registration-confirmations-soutien')
            ->setDescription('Send registration confirmation emails to parents of students in soutien classes for the 2025-2026 school year');
    }


    /**
     * @throws \DateMalformedStringException
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        // Récupération des classes "soutien" pour l'année 2025-2026
        $studyClasses = $this->studyClassRepository->findBy([
            'classType'  => StudyClass::CLASS_TYPE_SOUTIEN,
            'schoolYear' => StudyClass::SCHOOL_YEAR_2025_2026,
        ]);

        $sent = 0;
        $skipped = 0;

        foreach ($studyClasses as $class) {
            /** @var StudyClass $class */
            if ($class->getId() === 81 || $class->getId() === 75) {
                $skipped++;
                continue;
            }

            // Récupération des inscriptions à cette classe
            $allStudentClass = $this->studentClassRegisteredRepository->findBy(['studyClass' => $class]);

            foreach ($allStudentClass as $studentClass) {
                /** @var StudentClassRegistered $studentClass */
                $student   = $studentClass->getStudent();
                $parent    = $student?->getParent();

                $parentContact = $parent?->getEmailContact();
                $parentName    = $parent?->getFullNameParent();
                $studentName   = $student?->getFullName();

                if (empty($parentContact)) {
                    // Pas d'email parent -> on saute et on log
                    $io->warning(sprintf(
                        'Aucun email parent pour %s (classe %s, ID %d)',
                        $studentName ?? 'élève inconnu',
                        $class->getName(),
                        $class->getId()
                    ));
                    $skipped++;
                    continue;
                }

                // Variables attendues par le template Twig:
                // - yearLabel
                // - parentFullName
                // - studentFullName
                // - studentDob (nullable)
                // - className
                // - classLevel
                // - day
                // - startHour (nullable)
                // - endHour   (nullable)
                // - speciality
                $context = [
                    'yearLabel'       => '2025–2026', // ou mappe depuis $class->getSchoolYear() si besoin
                    'parentFullName'  => $parentName,
                    'studentFullName' => $studentName,

                    'className'  => $class->getSpeciality(),
                    'classLevel' => $class->getLevel(),
                    'day'        => $class->getDay(),

                    // startHour / endHour doivent être \DateTimeInterface pour le filtre Twig |date('H:i')
                    'startHour'  => $class->getStartHour() instanceof \DateTimeInterface ? $class->getStartHour() : null,
                    'endHour'    => $class->getEndHour() instanceof \DateTimeInterface ? $class->getEndHour() : null,

                    'speciality' => $class->getSpeciality(), // adapte le nom du getter si différent
                ];

                // Envoi du mail
                $this->mailService->sendEmail(
                    to: $parentContact,
                    //to: 'issamlamri34000@gmail.com', // <-- pour tests
                    subject: 'Confirmation d\'inscription au soutien scolaire 2025–2026',
                    template: 'email/registration_enrollment_confirmation_soutien.html.twig',
                    context: $context,
                    sender: 'contact@ccib38.fr',senderName: 'CCIB38 Soutien scolaire'
                );
                $sent++;
            }
        }

        $this->entityManager->flush();

        $io->success(sprintf(
            'Envois terminés. %d e-mails envoyés, %d inscriptions ignorées.',
            $sent,
            $skipped
        ));

        return Command::SUCCESS;
    }
}
