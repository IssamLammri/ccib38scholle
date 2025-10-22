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
     * Recherche paginée + filtres.
     *
     * @param array{
     *   parentId?: int,
     *   status?: string,
     *   dateFrom?: \DateTimeInterface,
     *   dateTo?: \DateTimeInterface,
     *   q?: string,
     *   page?: int,
     *   limit?: int
     * } $criteria
     *
     * @return array{items: Refund[], total: int, page: int, limit: int}
     */
    public function search(array $criteria): array
    {
        $qb = $this->createQueryBuilder('r')
            ->leftJoin('r.parent', 'p')->addSelect('p')
            ->leftJoin('r.invoices', 'i')->addSelect('i')
            ->orderBy('r.refundDate', 'DESC');

        if (!empty($criteria['parentId'])) {
            $qb->andWhere('p.id = :pid')->setParameter('pid', (int)$criteria['parentId']);
        }

        if (!empty($criteria['status'])) {
            $qb->andWhere('r.status = :status')->setParameter('status', $criteria['status']);
        }

        if (!empty($criteria['dateFrom'])) {
            $qb->andWhere('r.refundDate >= :df')->setParameter('df', $criteria['dateFrom']);
        }

        if (!empty($criteria['dateTo'])) {
            $qb->andWhere('r.refundDate <= :dt')->setParameter('dt', $criteria['dateTo']);
        }

        if (!empty($criteria['q'])) {
            $qb->andWhere('r.comment LIKE :q OR r.reference LIKE :q OR r.method LIKE :q')
                ->setParameter('q', '%'.$criteria['q'].'%');
        }

        $page  = max(1, (int)($criteria['page'] ?? 1));
        $limit = max(1, min(100, (int)($criteria['limit'] ?? 20)));
        $qb->setFirstResult(($page - 1) * $limit)->setMaxResults($limit);

        $items = $qb->getQuery()->getResult();

        // total
        $countQb = clone $qb;
        $countQb->resetDQLPart('orderBy')->resetDQLPart('join')->select('COUNT(DISTINCT r.id)');
        $total = (int)$countQb->getQuery()->getSingleScalarResult();

        return ['items' => $items, 'total' => $total, 'page' => $page, 'limit' => $limit];
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
