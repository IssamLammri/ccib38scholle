<?php

namespace App\Repository;

use App\Entity\Session;
use App\Entity\StudyClass;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

class SessionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Session::class);
    }

    public function findFutureSessions(StudyClass $studyClass)
    {
        return $this->createQueryBuilder('s')
            ->where('s.studyClass = :studyClass')
            ->andWhere('s.startTime >= :now')
            ->setParameter('studyClass', $studyClass)
            ->setParameter('now', new \DateTime())
            ->getQuery()
            ->getResult();
    }

    public function findSessionsWithPaginationAndSearch(int $page, int $limit, string $search = '', ?User $user = null): Paginator
    {
        $queryBuilder = $this->createQueryBuilder('s')
            ->join('s.room', 'r')
            ->join('s.teacher', 't')
            ->join('s.studyClass', 'sc');

        // Filtrage par recherche
        if (!empty($search)) {
            $queryBuilder
                ->andWhere('r.name LIKE :search OR t.lastName LIKE :search OR sc.name LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }

        if ($user !== null) {
            $queryBuilder
                ->andWhere('t.user = :user') // Vérifie si le User associé au Teacher correspond
                ->setParameter('user', $user);
        }

        // Pagination
        $queryBuilder
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit);

        return new Paginator($queryBuilder);
    }
}
