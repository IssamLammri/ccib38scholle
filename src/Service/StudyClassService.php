<?php
namespace App\Service;

use App\Entity\StudyClass;
use App\Entity\Teacher;
use App\Repository\RoomRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class StudyClassService
{
    private EntityManagerInterface $em;
    private ValidatorInterface $validator;

    /** Séquence des étapes dans l’ordre */

    public function __construct(
        EntityManagerInterface $em,
        ValidatorInterface $validator,
        private RoomRepository $roomRepository
    ) {
        $this->em          = $em;
        $this->validator = $validator;
    }

    /**
     * Met à jour une StudyClass à partir d'un tableau de données brutes.
     * Retourne un tableau d’erreurs (français) ou null si OK.
     * @throws \Exception si date invalide
     */
    public function updateFromArray(StudyClass $studyClass, array $data): ?array
    {
        if (isset($data['name'])) {
            $studyClass->setName((string) $data['name']);
        }
        if (isset($data['level'])) {
            $studyClass->setLevel($data['level']);
        }
        if (isset($data['speciality'])) {
            $studyClass->setSpeciality((string) $data['speciality']);
        }
        if (isset($data['classType'])) {
            $studyClass->setClassType((string) $data['classType']);
        }
        if (isset($data['day'])) {
            $studyClass->setDay((string) $data['day']);
        }
        if (!empty($data['startHour'])) {
            $studyClass->setStartHour(
                new \DateTimeImmutable($data['startHour'], new \DateTimeZone('Europe/Paris'))
            );
        }
        if (!empty($data['endHour'])) {
            $studyClass->setEndHour(
                new \DateTimeImmutable($data['endHour'], new \DateTimeZone('Europe/Paris'))
            );
        }
        dump($data);
        if (array_key_exists('schoolYear', $data)) {
            $studyClass->setSchoolYear($data['schoolYear'] ? : null);
        }

        if (array_key_exists('principalRoomId', $data)) {
            $room = $data['principalRoomId']
                ? $this->roomRepository->findOneBy(['id' => $data['principalRoomId']])
                : null;
            $studyClass->setPrincipalRoom($room);
        }

        if (array_key_exists('principalTeacherId', $data)) {
            $teacher = $data['principalTeacherId']
                ? $this->em->getRepository(Teacher::class)
                    ->find($data['principalTeacherId'])
                : null;
            $studyClass->setPrincipalTeacher($teacher);
        }

        // validation
        $errors = [];
        $violations = $this->validator->validate($studyClass);
        if (count($violations) > 0) {
            foreach ($violations as $v) {
                $errors[$v->getPropertyPath()] = $v->getMessage();
            }
            return $errors;
        }

        $this->em->persist($studyClass);
        $this->em->flush();

        return null;
    }
}
