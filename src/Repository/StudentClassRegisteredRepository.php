<?php

namespace App\Repository;

use App\Entity\StudentClassRegistered;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class StudentClassRegisteredRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, StudentClassRegistered::class);
    }

    public function findStudentsInStudyClass($studyClass){
        return $this->createQueryBuilder('scr')
            ->where('scr.studyClass = :studyClass')
            ->setParameter('studyClass', $studyClass)
            ->getQuery()
            ->getResult();
    }

}
