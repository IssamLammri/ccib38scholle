<?php

namespace App\Repository;

use App\Entity\Payment;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Payment>
 */
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

    public function findPaymentsBetweenDates(\DateTimeImmutable $startDate, \DateTimeImmutable $endDate): array
    {
        return $this->createQueryBuilder('p')
            ->where('p.paymentDate BETWEEN :startDate AND :endDate')
            ->setParameter('startDate', $startDate->format('Y-m-d'))
            ->setParameter('endDate', $endDate->format('Y-m-d'))
            ->getQuery()
            ->getResult();
    }

    /**
     * Récupère tous les paiements liés à une année scolaire donnée.
     * On ne filtre PAS par 'classType' ici pour être sûr d'avoir tout l'argent versé
     * pour cette année scolaire (ex: frais divers, inscriptions, etc.).
     *
     * @param string $schoolYear (ex: "2023-2024")
     * @return Payment[]
     */
    public function findAllPayementsForSchoolYear(string $schoolYear): array
    {
        $years = explode('/', $schoolYear);

        $startYearInt = isset($years[0]) ? (int)$years[0] : (int)date('Y');

        $startDate = new \DateTimeImmutable($startYearInt . '-05-01');
        $endDate   = new \DateTimeImmutable(($startYearInt + 1) . '-08-31');

        $qb = $this->createQueryBuilder('p');

        return $qb
            ->leftJoin('p.studyClass', 'sc')
            ->addSelect('sc')

            ->innerJoin('p.parent', 'par')
            ->addSelect('par')

            ->andWhere($qb->expr()->orX(
                'sc.schoolYear = :schoolYear',
                'p.paymentDate BETWEEN :startDate AND :endDate'
            ))

            ->andWhere('p.serviceType != :excludedType')

            ->setParameter('schoolYear', $schoolYear)
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->setParameter('excludedType', 'livres')

            ->orderBy('p.paymentDate', 'DESC')
            ->getQuery()
            ->getResult();
    }
}