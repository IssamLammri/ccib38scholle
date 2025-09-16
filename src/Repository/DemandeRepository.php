<?php
// src/Repository/DemandeRepository.php
namespace App\Repository;

use App\Entity\Demande;
use App\Entity\ParentEntity;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class DemandeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Demande::class);
    }

    /** @return Demande[] */
    public function findLatestByParent(ParentEntity $parent, int $limit = 10): array
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.parent = :p')->setParameter('p', $parent)
            ->orderBy('d.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()->getResult();
    }
}
