<?php

namespace App\Repository;

use App\Entity\ParentEntity;
use App\Entity\Student;
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

    public function findStudentsActiveInStudyClass($studyClass){
        return $this->createQueryBuilder('scr')
            ->where('scr.studyClass = :studyClass')
            ->andWhere('scr.active = :active')
            ->setParameter('studyClass', $studyClass)
            ->setParameter('active', true)
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

    public function findRegisteredForUnpaidParents(\DateTimeImmutable $startDate)
    {
        return $this->createQueryBuilder('scr')
            ->where('scr.createdAt < :startDate')
            ->andWhere('scr.active IS NULL OR scr.active = true') // Gère les NULL et true
            ->setParameter('startDate', $startDate)
            ->getQuery()
            ->getResult();
    }

    /** @return StudentClassRegistered[] */
    public function findActiveByStudent(Student $student): array
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.student = :s')->setParameter('s', $student)
            ->andWhere('r.active = true')
            ->orderBy('r.createdAt', 'DESC')
            ->getQuery()->getResult();
    }

    /** @return StudentClassRegistered[] */
    public function findAllActiveByParent(ParentEntity $parent): array
    {
        return $this->createQueryBuilder('r')
            ->join('r.student','st')->addSelect('st')
            ->andWhere('st.parent = :p')->setParameter('p', $parent)
            ->andWhere('r.active = true')
            ->getQuery()->getResult();
    }

    public function findStudentsActiveInStudyClassBySchoolYear($schoolYear): array
    {
        return $this->createQueryBuilder('r')
            ->join('r.student', 'st')->addSelect('st')
            ->join('st.parent', 'p')->addSelect('p')
            ->join('r.studyClass', 'sc')->addSelect('sc')
            ->andWhere('sc.schoolYear = :sy')
            ->andWhere('sc.classType IN (:types)')
            ->setParameter('sy', $schoolYear)
            ->setParameter('types', [StudyClass::CLASS_TYPE_SOUTIEN, StudyClass::CLASS_TYPE_ARABE])
            ->getQuery()
            ->getResult();
    }
}
