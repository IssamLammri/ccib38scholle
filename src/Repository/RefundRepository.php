<?php

namespace App\Repository;

use App\Entity\Refund;
use App\Entity\ParentEntity;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class RefundRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Refund::class);
    }


    /**
     * Total remboursé pour un parent donné (option période).
     */
    public function totalByParent(ParentEntity $parent, ?\DateTimeInterface $from = null, ?\DateTimeInterface $to = null): string
    {
        $qb = $this->createQueryBuilder('r')
            ->select('COALESCE(SUM(r.amount), 0)')
            ->andWhere('r.parent = :p')->setParameter('p', $parent)
            ->andWhere('r.status = :processed')->setParameter('processed', 'processed');

        if ($from) { $qb->andWhere('r.refundDate >= :from')->setParameter('from', $from); }
        if ($to)   { $qb->andWhere('r.refundDate <= :to')->setParameter('to', $to); }

        return (string)$qb->getQuery()->getSingleScalarResult();
    }
}
