<?php

namespace App\Repository;

use App\Entity\ParentEntity;
use App\Entity\Student;
use App\Entity\StudyClass;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class StudentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Student::class);
    }

    public function findStudentsNotInStudyClass(StudyClass $studyClass)
    {
        return $this->createQueryBuilder('s')
            ->where('s.id NOT IN (
            SELECT IDENTITY(sc.student)
            FROM App\Entity\StudentClassRegistered sc
            WHERE sc.studyClass = :studyClass
        )')
            ->setParameter('studyClass', $studyClass)
            ->getQuery()
            ->getResult();
    }

}
