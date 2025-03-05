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

    public function findSessionsBetweenDates(\DateTimeImmutable $startDate, \DateTimeImmutable $endDate): array
    {
        $qb = $this->createQueryBuilder('s')
            ->where('s.startTime >= :startDate')
            ->andWhere('s.endTime <= :endDate')
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate);

        return $qb->getQuery()->getResult();
    }

    /**
     * Retourne toutes les sessions correspondant aux filtres.
     *
     * @param string $search
     * @param int|null $month Un mois (1-12) à filtrer selon la date de début
     * @param int|null $classId L'ID de la classe à filtrer
     * @param string|null $speciality La spécialité à filtrer (partie ou totalité)
     * @param int|null $teacherId L'ID de l'enseignant à filtrer
     * @param User|null $user Si défini, limite aux sessions du teacher lié à l'utilisateur
     *
     * @return Session[]
     */
    public function findSessionsWithSearch(
        string $search = '',
        ?int $month = null,
        ?int $classId = null,
        ?string $speciality = null,
        ?int $teacherId = null,
        ?User $user = null
    ): array {
        $qb = $this->createQueryBuilder('s')
            ->join('s.room', 'r')
            ->join('s.teacher', 't')
            ->join('s.studyClass', 'sc');

        // Recherche textuelle sur salle, enseignant, classe ou spécialité
        if (!empty($search)) {
            $qb->andWhere('r.name LIKE :search OR t.lastName LIKE :search OR sc.name LIKE :search OR sc.speciality LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }

        // Filtrer par mois (sur startTime)
        if ($month) {
            // Utilisation de MONTH() si la fonction est enregistrée
            $qb->andWhere('MONTH(s.startTime) = :month')
                ->setParameter('month', $month);
        }

        // Filtrer par classe (par ID)
        if ($classId) {
            $qb->andWhere('sc.id = :classId')
                ->setParameter('classId', $classId);
        }

        // Filtrer par spécialité
        if ($speciality) {
            $qb->andWhere('sc.speciality LIKE :speciality')
                ->setParameter('speciality', '%' . $speciality . '%');
        }

        // Filtrer par enseignant (par ID)
        if ($teacherId) {
            $qb->andWhere('t.id = :teacherId')
                ->setParameter('teacherId', $teacherId);
        }

        // Si un utilisateur est fourni, on limite aux sessions dont le teacher correspond
        if ($user !== null) {
            $qb->andWhere('t.user = :user')
                ->setParameter('user', $user);
        }

        // Tri par date de début décroissante
        $qb->orderBy('s.startTime', 'DESC');

        return $qb->getQuery()->getResult();
    }
}
