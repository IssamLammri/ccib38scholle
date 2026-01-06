<?php

namespace App\Repository;

use App\Entity\StudyClass;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class StudyClassRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, StudyClass::class);
    }

    public function getStudentCounts(): array
    {
        $qb = $this->createQueryBuilder('sc')
            ->select('sc.id, COUNT(scr.id) as studentCount')
            ->andWhere('sc.active = true')
            ->leftJoin('App\Entity\StudentClassRegistered', 'scr', 'WITH', 'scr.studyClass = sc')
            ->groupBy('sc.id');

        return $qb->getQuery()->getResult();
    }
}
