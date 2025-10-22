<?php

namespace App\Repository;

use App\Entity\ParentEntity;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ParentsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ParentEntity::class);
    }

    /**
     * Recherche des parents par nom, prénom ou email (père/mère).
     *
     * @param string $query
     * @param int $limit
     * @return ParentEntity[]
     */
    public function searchByNameOrEmail(string $query, int $limit = 20): array
    {
        $qb = $this->createQueryBuilder('p');

        $qb->where(
            $qb->expr()->orX(
                $qb->expr()->like('LOWER(p.fatherLastName)', ':q'),
                $qb->expr()->like('LOWER(p.fatherFirstName)', ':q'),
                $qb->expr()->like('LOWER(p.motherLastName)', ':q'),
                $qb->expr()->like('LOWER(p.motherFirstName)', ':q'),
                $qb->expr()->like('LOWER(p.fatherEmail)', ':q'),
                $qb->expr()->like('LOWER(p.motherEmail)', ':q')
            )
        )
            ->setParameter('q', '%' . mb_strtolower(trim($query)) . '%')
            ->orderBy('p.fatherLastName', 'ASC')
            ->addOrderBy('p.motherLastName', 'ASC')
            ->setMaxResults($limit);

        return $qb->getQuery()->getResult();
    }
}
