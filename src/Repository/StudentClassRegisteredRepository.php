<?php

namespace App\Repository;

use App\Entity\StudentClassRegistered;
use App\Entity\StudyClass;
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

    public function findFutureSessions(StudyClass $studyClass)
    {
        return $this->createQueryBuilder('scr')
            ->join('scr.studyClass', 'sc')
            ->join('sc.sessions', 's')
            ->where('scr.studyClass = :studyClass')
            ->andWhere('s.date >= :now')
            ->setParameter('studyClass', $studyClass)
            ->setParameter('now', new \DateTime())
            ->getQuery()
            ->getResult();
    }

}
