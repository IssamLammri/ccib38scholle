<?php
// src/Command/UpdateStudyClassForStudentsCommand.php

namespace App\Command;

use App\Entity\StudentClassRegistered;
use App\Entity\StudyClass;
use App\Repository\PaymentRepository;
use App\Repository\RegistrationArabicCoursRepository;
use App\Repository\StudentClassRegisteredRepository;
use App\Repository\StudyClassRepository;
use App\Service\RegistrationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class UpdateStudyClassForStudentsCommand extends Command
{
    protected static $defaultName = 'app:update-study-class-for-students';

    private RegistrationService $registrationService;
    private PaymentRepository $paymentRepository;
    private RegistrationArabicCoursRepository $registrationRepo;

    public function __construct(
        RegistrationService $registrationService,
        PaymentRepository $paymentRepository,
        RegistrationArabicCoursRepository $registrationRepo,
        private StudyClassRepository $studyClassRepository,
        private StudentClassRegisteredRepository $classRegisteredRepository,
        private EntityManagerInterface $entityManager
    ) {
        parent::__construct(self::$defaultName);
        $this->registrationService = $registrationService;
        $this->paymentRepository   = $paymentRepository;
        $this->registrationRepo    = $registrationRepo;
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Met à jour le niveau des étudiants inscrits aux classes d’Arabe en fonction de leur StudyClass.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('Mise à jour des StudyClass pour les étudiants');

        $studysClass = $this->studyClassRepository->findBy([
            'speciality' => 'Arabe',
        ]);

        if (empty($studysClass)) {
            $io->warning('Aucune classe trouvée pour la spécialité "Arabe".');
            return Command::SUCCESS;
        }

        $totalStudentsUpdated = 0;
        $rows = [];

        foreach ($studysClass as $studyClass) {
            /** @var StudyClass $studyClass */
            $allRegisteredStudent = $this->classRegisteredRepository->findBy([
                'studyClass' => $studyClass,
            ]);

            $updatedForClass = 0;
            foreach ($allRegisteredStudent as $registeredStudent) {
                /** @var StudentClassRegistered $registeredStudent */
                $student = $registeredStudent->getStudent();
                $student->setLevel($studyClass->getLevel());
                $updatedForClass++;
                $totalStudentsUpdated++;
            }

            $rows[] = [
                $studyClass->getId(),
                $studyClass->getName() ?? '(sans nom)',
                $studyClass->getLevel(),
                $updatedForClass
            ];
        }

        $this->entityManager->flush();

        // Affichage tableau récapitulatif
        $io->section('Résumé des mises à jour');
        $io->table(
            ['ID Classe', 'Nom Classe', 'Niveau', 'Étudiants mis à jour'],
            $rows
        );

        $io->success(sprintf(
            '%d étudiants ont été mis à jour dans %d classes.',
            $totalStudentsUpdated,
            count($studysClass)
        ));

        return Command::SUCCESS;
    }
}
