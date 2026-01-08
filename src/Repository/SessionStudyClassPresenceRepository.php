<?php

namespace App\Repository;

use App\Entity\Session;
use App\Entity\SessionStudyClassPresence;
use App\Entity\StudyClass;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class SessionStudyClassPresenceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SessionStudyClassPresence::class);
    }

    public function paginate(
        int $page,
        int $limit,
        ?int $teacherId = null,
        ?\DateTimeImmutable $from = null,
        ?\DateTimeImmutable $to = null
    ): array {
        $page = max(1, $page);
        $limit = max(1, min(100, $limit));

        $schoolYear = StudyClass::SCHOOL_YEAR_ACTIVE;

        $qb = $this->createQueryBuilder('p')
            ->innerJoin('p.session', 'se')
            ->innerJoin('se.studyClass', 'sc')
            ->andWhere('sc.schoolYear = :sy')
            ->setParameter('sy', $schoolYear)
            ->orderBy('p.id', 'DESC')
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit);

        if ($teacherId) {
            $qb->andWhere('se.teacher = :tid')->setParameter('tid', $teacherId);
        }
        if ($from) {
            $qb->andWhere('se.startTime >= :from')->setParameter('from', $from);
        }
        if ($to) {
            $qb->andWhere('se.startTime <= :to')->setParameter('to', $to);
        }

        $items = $qb->getQuery()->getResult();

        $countQb = $this->createQueryBuilder('p')
            ->innerJoin('p.session', 'se')
            ->innerJoin('se.studyClass', 'sc')
            ->select('COUNT(p.id)')
            ->andWhere('sc.schoolYear = :sy')
            ->setParameter('sy', $schoolYear);

        if ($teacherId) {
            $countQb->andWhere('se.teacher = :tid')->setParameter('tid', $teacherId);
        }
        if ($from) {
            $countQb->andWhere('se.startTime >= :from')->setParameter('from', $from);
        }
        if ($to) {
            $countQb->andWhere('se.startTime <= :to')->setParameter('to', $to);
        }

        $total = (int) $countQb->getQuery()->getSingleScalarResult();

        return ['items' => $items, 'total' => $total];
    }

    public function paginateByStudentStats(
        int $page,
        int $limit,
        ?int $studyClassId = null,
        ?string $classType = null,
        ?string $q = null,
        ?int $teacherId = null,
        ?\DateTimeImmutable $from = null,
        ?\DateTimeImmutable $to = null
    ): array {
        $page = max(1, $page);
        $limit = max(1, min(100, $limit));

        $schoolYear = StudyClass::SCHOOL_YEAR_ACTIVE;

        $qb = $this->createQueryBuilder('p')
            ->innerJoin('p.student', 's')
            ->innerJoin('p.session', 'se')
            ->innerJoin('se.studyClass', 'sc')
            ->andWhere('sc.schoolYear = :sy')
            ->setParameter('sy', $schoolYear)
            ->select([
                's.id AS studentId',
                's.firstName AS firstName',
                's.lastName AS lastName',
                's.level AS level',
                'sc.id AS studyClassId',
                'sc.name AS className',
                'sc.level AS classLevel',
                'sc.classType AS classType',
                'COUNT(p.id) AS total',
                "SUM(CASE WHEN p.isPresent = true THEN 1 ELSE 0 END) AS presents",
                "SUM(CASE WHEN p.isPresent = false THEN 1 ELSE 0 END) AS absents",
                "SUM(CASE WHEN p.isPresent IS NULL THEN 1 ELSE 0 END) AS notMarked",
            ])
            ->groupBy('s.id, s.firstName, s.lastName, s.level, sc.id, sc.name, sc.level, sc.classType')
            ->orderBy('absents', 'DESC')
            ->addOrderBy('s.lastName', 'ASC')
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit);

        if ($studyClassId) {
            $qb->andWhere('sc.id = :scid')->setParameter('scid', $studyClassId);
        }
        if ($classType) {
            $qb->andWhere('sc.classType = :ctype')->setParameter('ctype', $classType);
        }
        if ($q !== null && trim($q) !== '') {
            $qq = '%' . mb_strtolower(trim($q)) . '%';
            $qb->andWhere('LOWER(s.firstName) LIKE :q OR LOWER(s.lastName) LIKE :q')
                ->setParameter('q', $qq);
        }
        if ($teacherId) {
            $qb->andWhere('se.teacher = :tid')->setParameter('tid', $teacherId);
        }
        if ($from) {
            $qb->andWhere('se.startTime >= :from')->setParameter('from', $from);
        }
        if ($to) {
            $qb->andWhere('se.startTime <= :to')->setParameter('to', $to);
        }

        $items = $qb->getQuery()->getArrayResult();

        $countQb = $this->createQueryBuilder('p')
            ->innerJoin('p.student', 's')
            ->innerJoin('p.session', 'se')
            ->innerJoin('se.studyClass', 'sc')
            ->andWhere('sc.schoolYear = :sy')
            ->setParameter('sy', $schoolYear)
            ->select("COUNT(DISTINCT CONCAT(s.id, '-', sc.id))");

        if ($studyClassId) {
            $countQb->andWhere('sc.id = :scid')->setParameter('scid', $studyClassId);
        }
        if ($classType) {
            $countQb->andWhere('sc.classType = :ctype')->setParameter('ctype', $classType);
        }
        if ($q !== null && trim($q) !== '') {
            $qq = '%' . mb_strtolower(trim($q)) . '%';
            $countQb->andWhere('LOWER(s.firstName) LIKE :q OR LOWER(s.lastName) LIKE :q')
                ->setParameter('q', $qq);
        }
        if ($teacherId) {
            $countQb->andWhere('se.teacher = :tid')->setParameter('tid', $teacherId);
        }
        if ($from) {
            $countQb->andWhere('se.startTime >= :from')->setParameter('from', $from);
        }
        if ($to) {
            $countQb->andWhere('se.startTime <= :to')->setParameter('to', $to);
        }

        $total = (int) $countQb->getQuery()->getSingleScalarResult();

        return ['items' => $items, 'total' => $total];
    }

    public function paginateHistoryByStudent(
        int $studentId,
        int $page,
        int $limit,
        ?string $classType = null,
        ?int $teacherId = null,
        ?\DateTimeImmutable $from = null,
        ?\DateTimeImmutable $to = null
    ): array {
        $page = max(1, $page);
        $limit = max(1, min(100, $limit));

        $schoolYear = StudyClass::SCHOOL_YEAR_ACTIVE;

        $qb = $this->createQueryBuilder('p')
            ->innerJoin('p.student', 's')
            ->innerJoin('p.session', 'se')
            ->innerJoin('se.studyClass', 'sc')
            ->innerJoin('se.teacher', 't')
            ->andWhere('s.id = :sid')->setParameter('sid', $studentId)
            ->andWhere('sc.schoolYear = :sy')->setParameter('sy', $schoolYear);

        if ($classType) {
            $qb->andWhere('sc.classType = :ctype')->setParameter('ctype', $classType);
        }
        if ($teacherId) {
            $qb->andWhere('se.teacher = :tid')->setParameter('tid', $teacherId);
        }
        if ($from) {
            $qb->andWhere('se.startTime >= :from')->setParameter('from', $from);
        }
        if ($to) {
            $qb->andWhere('se.startTime <= :to')->setParameter('to', $to);
        }

        $qb->select([
            'p.id AS presenceId',
            'p.isPresent AS isPresent',
            'p.createdAt AS createdAt',
            'se.id AS sessionId',
            'se.startTime AS startTime',
            'se.endTime AS endTime',
            'sc.id AS studyClassId',
            'sc.name AS className',
            'sc.classType AS classType',
            't.firstName AS teacherFirstName',
            't.lastName AS teacherLastName',
        ])
            ->orderBy('se.startTime', 'DESC')
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit);

        $items = $qb->getQuery()->getArrayResult();

        $countQb = $this->createQueryBuilder('p')
            ->innerJoin('p.student', 's')
            ->innerJoin('p.session', 'se')
            ->innerJoin('se.studyClass', 'sc')
            ->select('COUNT(p.id)')
            ->andWhere('s.id = :sid')->setParameter('sid', $studentId)
            ->andWhere('sc.schoolYear = :sy')->setParameter('sy', $schoolYear);

        if ($classType) {
            $countQb->andWhere('sc.classType = :ctype')->setParameter('ctype', $classType);
        }
        if ($teacherId) {
            $countQb->andWhere('se.teacher = :tid')->setParameter('tid', $teacherId);
        }
        if ($from) {
            $countQb->andWhere('se.startTime >= :from')->setParameter('from', $from);
        }
        if ($to) {
            $countQb->andWhere('se.startTime <= :to')->setParameter('to', $to);
        }

        $total = (int) $countQb->getQuery()->getSingleScalarResult();

        return ['items' => $items, 'total' => $total];
    }

    public function paginateByStudentServiceStats(
        int $page,
        int $limit,
        ?string $classType = null,
        ?string $q = null,
        ?int $teacherId = null,
        ?\DateTimeImmutable $from = null,
        ?\DateTimeImmutable $to = null
    ): array {
        $page = max(1, $page);
        $limit = max(1, min(100, $limit));

        $schoolYear = StudyClass::SCHOOL_YEAR_ACTIVE;

        $qb = $this->createQueryBuilder('p')
            ->innerJoin('p.student', 's')
            ->innerJoin('p.session', 'se')
            ->innerJoin('se.studyClass', 'sc')
            ->andWhere('sc.schoolYear = :sy')->setParameter('sy', $schoolYear)
            ->select([
                's.id AS studentId',
                's.firstName AS firstName',
                's.lastName AS lastName',
                's.level AS level',
                'sc.classType AS classType',
                'COUNT(p.id) AS total',
                "SUM(CASE WHEN p.isPresent = true THEN 1 ELSE 0 END) AS presents",
                "SUM(CASE WHEN p.isPresent = false THEN 1 ELSE 0 END) AS absents",
                "SUM(CASE WHEN p.isPresent IS NULL THEN 1 ELSE 0 END) AS notMarked",
            ])
            ->groupBy('s.id, s.firstName, s.lastName, s.level, sc.classType')
            ->orderBy('absents', 'DESC')
            ->addOrderBy('s.lastName', 'ASC')
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit);

        if ($classType) {
            $qb->andWhere('sc.classType = :ctype')->setParameter('ctype', $classType);
        }
        if ($q !== null && trim($q) !== '') {
            $qq = '%' . mb_strtolower(trim($q)) . '%';
            $qb->andWhere('LOWER(s.firstName) LIKE :q OR LOWER(s.lastName) LIKE :q')
                ->setParameter('q', $qq);
        }
        if ($teacherId) {
            $qb->andWhere('se.teacher = :tid')->setParameter('tid', $teacherId);
        }
        if ($from) {
            $qb->andWhere('se.startTime >= :from')->setParameter('from', $from);
        }
        if ($to) {
            $qb->andWhere('se.startTime <= :to')->setParameter('to', $to);
        }

        $items = $qb->getQuery()->getArrayResult();

        $countQb = $this->createQueryBuilder('p')
            ->innerJoin('p.student', 's')
            ->innerJoin('p.session', 'se')
            ->innerJoin('se.studyClass', 'sc')
            ->andWhere('sc.schoolYear = :sy')->setParameter('sy', $schoolYear)
            ->select("COUNT(DISTINCT CONCAT(s.id, '-', sc.classType))");

        if ($classType) {
            $countQb->andWhere('sc.classType = :ctype')->setParameter('ctype', $classType);
        }
        if ($q !== null && trim($q) !== '') {
            $qq = '%' . mb_strtolower(trim($q)) . '%';
            $countQb->andWhere('LOWER(s.firstName) LIKE :q OR LOWER(s.lastName) LIKE :q')
                ->setParameter('q', $qq);
        }
        if ($teacherId) {
            $countQb->andWhere('se.teacher = :tid')->setParameter('tid', $teacherId);
        }
        if ($from) {
            $countQb->andWhere('se.startTime >= :from')->setParameter('from', $from);
        }
        if ($to) {
            $countQb->andWhere('se.startTime <= :to')->setParameter('to', $to);
        }

        $total = (int) $countQb->getQuery()->getSingleScalarResult();

        return ['items' => $items, 'total' => $total];
    }

    public function countWithPresenceFilled(Session $session): int
    {
        return (int) $this->createQueryBuilder('p')
            ->select('COUNT(p.id)')
            ->andWhere('p.session = :session')
            ->andWhere('p.isPresent IS NOT NULL')
            ->setParameter('session', $session)
            ->getQuery()
            ->getSingleScalarResult();
    }

}
