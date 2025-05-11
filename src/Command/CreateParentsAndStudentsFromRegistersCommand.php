<?php

namespace App\Command;

use App\Entity\ParentEntity;
use App\Entity\Student;
use App\Repository\ParentsRepository;
use App\Repository\RegistrationArabicCoursRepository;
use App\Repository\StudentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class CreateParentsAndStudentsFromRegistersCommand extends Command
{
    protected static $defaultName = 'app:create-sessions'; // Définit un nom valide pour la commande

    private EntityManagerInterface $entityManager;
    private RegistrationArabicCoursRepository $registrationArabicCoursRepository;

    private ParentsRepository $parentsRepository;

    private StudentRepository $studentRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        RegistrationArabicCoursRepository $registrationArabicCoursRepository,
        ParentsRepository $parentsRepository,
        StudentRepository $studentRepository
    ){
        parent::__construct();
        $this->entityManager = $entityManager;
        $this->registrationArabicCoursRepository = $registrationArabicCoursRepository;
        $this->parentsRepository = $parentsRepository;
        $this->studentRepository = $studentRepository;
    }

    protected function configure(): void
    {
        $this
            ->setName('app:create-parents-and-students')
            ->setDescription('Create sessions based on provided parameters and data.');
    }


    /**
     * @throws \DateMalformedStringException
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $allRegistered = $this->registrationArabicCoursRepository->findAll();
        foreach ($allRegistered as $registration) {
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
        }
        $this->entityManager->flush();

        $io->success('Parents and students created successfully from registers.');

        return Command::SUCCESS;
    }
}
