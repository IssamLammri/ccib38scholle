<?php

namespace App\Repository;

use App\Entity\Payment;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class PaymentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Payment::class);
    }

    public function findPaymentsByParent(User $parent)
    {
        return $this->createQueryBuilder('p')
            ->where('p.parent = :parent')
            ->setParameter('parent', $parent)
            ->getQuery()
            ->getResult();
    }

}
