<?php

namespace App\Command;

use App\Entity\Session;
use App\Entity\Room;
use App\Entity\Teacher;
use App\Entity\StudyClass;
use App\Entity\SessionStudyClassPresence;
use App\Repository\StudentClassRegisteredRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class CreateSessionsCommand extends Command
{
    protected static $defaultName = 'app:create-sessions'; // Définit un nom valide pour la commande

    private EntityManagerInterface $entityManager;
    private StudentClassRegisteredRepository $studentClassRegisteredRepository;

    public function __construct(EntityManagerInterface $entityManager, StudentClassRegisteredRepository $studentClassRegisteredRepository)
    {
        parent::__construct();
        $this->entityManager = $entityManager;
        $this->studentClassRegisteredRepository = $studentClassRegisteredRepository;
    }

    protected function configure(): void
    {
        $this
            ->setName('app:create-sessions')
            ->setDescription('Create sessions based on provided parameters and data.');
    }


    /**
     * @throws \DateMalformedStringException
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        // Sample data for session creation
        $dataSessions = [
            [
                'start_date' => '2024-12-18 11:00:00',
                'end_date' => '2024-12-18 12:30:00',
                'room_id' => 1,
                'teacher_id' => 2,
                'study_class_id' => 3
            ],
            [
                'start_date' => '2024-12-18 13:00:00',
                'end_date' => '2024-12-18 14:30:00',
                'room_id' => 2,
                'teacher_id' => 3,
                'study_class_id' => 4
            ]
        ];

        foreach ($dataSessions as $data) {
            $startDate = new \DateTimeImmutable($data['start_date']);
            $endDate = new \DateTimeImmutable($data['end_date']);
            $room = $this->entityManager->getRepository(Room::class)->find($data['room_id']);
            $teacher = $this->entityManager->getRepository(Teacher::class)->find($data['teacher_id']);
            $studyClass = $this->entityManager->getRepository(StudyClass::class)->find($data['study_class_id']);

            if (!$room || !$teacher || !$studyClass) {
                $io->error("Invalid Room, Teacher, or Study Class ID for session starting at {$data['start_date']}.");
                continue;
            }

            $session = new Session();
            $session->setStartTime($startDate);
            $session->setEndTime($endDate);
            $session->setRoom($room);
            $session->setTeacher($teacher);
            $session->setStudyClass($studyClass);

            $this->entityManager->persist($session);

            $allStudentsToAddASession = $this->studentClassRegisteredRepository->findStudentsActiveInStudyClass($studyClass);
            foreach ($allStudentsToAddASession as $student) {
                $sessionStudyClassPresence = new SessionStudyClassPresence($student->getStudent(), $session);
                $this->entityManager->persist($sessionStudyClassPresence);
            }
        }

        $this->entityManager->flush();

        $io->success('All sessions have been created successfully!');

        return Command::SUCCESS;
    }
}
