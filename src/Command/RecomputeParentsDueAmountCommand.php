<?php

namespace App\Command;

use App\Entity\ParentEntity;
use App\Entity\StudyClass;
use App\Entity\Student;
use App\Entity\StudentClassRegistered;
use App\Repository\ParentsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:recompute-parents-due',
    description: 'Calcule et met à jour amountDueArabic et amountDueSoutien pour les parents.'
)]
class RecomputeParentsDueAmountCommand extends Command
{
    public function __construct(
        private readonly ParentsRepository $parentRepo,
        private readonly EntityManagerInterface $em,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('dry-run', null, InputOption::VALUE_NONE, 'Simule sans écrire en base')
            ->addOption('limit', null, InputOption::VALUE_REQUIRED, 'Limiter le nombre de parents traités', null)
            ->addOption('parent', null, InputOption::VALUE_REQUIRED, 'Traiter un seul parent (ID)', null)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $dryRun = (bool) $input->getOption('dry-run');
        $limit = $input->getOption('limit') !== null ? (int)$input->getOption('limit') : null;
        $onlyParentId = $input->getOption('parent') !== null ? (int)$input->getOption('parent') : null;

        $io->title('Recompute parents due amounts');
        $io->text('Mode: ' . ($dryRun ? 'DRY-RUN' : 'WRITE'));

        // Récupération des parents
        if ($onlyParentId) {
            $p = $this->parentRepo->find($onlyParentId);
            if (!$p) {
                $io->error("Parent #$onlyParentId introuvable.");
                return Command::FAILURE;
            }
            $parents = [$p];
        } else {
            $parents = $this->parentRepo->findAll();
            if ($limit !== null) {
                $parents = array_slice($parents, 0, $limit);
            }
        }

        $now = new \DateTimeImmutable('now');
        $processed = 0;
        $changed = 0;

        foreach ($parents as $parent) {
            /** @var ParentEntity $parent */
            $processed++;

            [$dueArabic, $dueSoutien] = $this->computeDueForParent($parent, $now);

            $beforeArabic = $parent->getAmountDueArabic();
            $beforeSoutien = $parent->getAmountDueSoutien();

            $parent->setAmountDueArabic($dueArabic);
            $parent->setAmountDueSoutien($dueSoutien);

            if ($beforeArabic !== $dueArabic || $beforeSoutien !== $dueSoutien) {
                $changed++;
            }

            if (!$dryRun && ($processed % 50 === 0)) {
                $this->em->flush();
            }
        }

        if ($dryRun) {
            $io->warning("DRY-RUN : aucune écriture en base. Parents simulés: $processed | Modifiés: $changed");
            return Command::SUCCESS;
        }

        $this->em->flush();
        $io->success("Terminé ✅ Parents traités: $processed | Modifiés: $changed");

        return Command::SUCCESS;
    }

    /**
     * Retourne [amountDueArabic, amountDueSoutien]
     */
    private function computeDueForParent(ParentEntity $parent, \DateTimeImmutable $now): array
    {
        /** @var iterable<Student> $students */
        $students = $parent->getStudents();

        // 1) ARABE : compter le nombre d'enfants ayant >=1 inscription active en Arabe
        $arabicStudentIds = [];

        // 2) SOUTIEN : par enfant, prendre la plus ancienne date d'inscription active en Soutien
        $soutienEarliestByStudent = []; // studentId => DateTimeImmutable

        foreach ($students as $student) {
            /** @var Student $student */
            $studentId = $student->getId();
            if (!$studentId) continue;

            /** @var iterable<StudentClassRegistered> $regs */
            $regs = $student->getRegistrations();

            foreach ($regs as $reg) {
                /** @var StudentClassRegistered $reg */

                // 1) inscription active = true
                if ($reg->isActive() !== true) {
                    continue;
                }

                // 2) classe existante
                $studyClass = $reg->getStudyClass();
                if (!$studyClass) {
                    continue;
                }

                // 3) studyClass active = true
                if ($studyClass->isActive() !== true) {
                    continue;
                }

                // 4) année scolaire = 2025/2026
                if ((string) $studyClass->getSchoolYear() !== '2025/2026') {
                    continue;
                }

                // 5) classType
                $classType = (string) $studyClass->getClassType();

                // --- ARABE ---
                if ($classType === StudyClass::CLASS_TYPE_ARABE) {
                    $arabicStudentIds[$studentId] = true;
                }

                // --- SOUTIEN ---
                if ($classType === StudyClass::CLASS_TYPE_SOUTIEN) {
                    $createdAt = $reg->getCreatedAt(); // DateTimeImmutable
                    if ($createdAt) {
                        if (
                            !isset($soutienEarliestByStudent[$studentId]) ||
                            $createdAt < $soutienEarliestByStudent[$studentId]
                        ) {
                            $soutienEarliestByStudent[$studentId] = $createdAt;
                        }
                    }
                }
            }
        }

        // --- Montant Arabe ---
        $arabicCount = count($arabicStudentIds);
        $dueArabic = $this->arabicPricing($arabicCount);

        // --- Montant Soutien ---
        // règle: 25€/mois, mois entiers à partir du mois suivant l'inscription
        $dueSoutien = 0;
        foreach ($soutienEarliestByStudent as $inscriptionDate) {
            $months = $this->fullMonthsFromInscription($inscriptionDate, $now);
            $dueSoutien += 25 * $months;
        }

        return [$dueArabic, $dueSoutien];
    }

    private function arabicPricing(int $childrenCount): int
    {
        return match (true) {
            $childrenCount <= 0 => 0,
            $childrenCount === 1 => 240,
            $childrenCount === 2 => 450,
            $childrenCount === 3 => 630,
            $childrenCount === 4 => 780,
            default => 200 * $childrenCount, // 5+ : 200€/enfant
        };
    }

    /**
     * Exemple attendu:
     * inscrit le 18/09/2025 -> mois complets: Oct, Nov, Dec, Jan = 4 (si on est en février)
     *
     * Méthode:
     * start = 1er jour du mois suivant inscription
     * end   = 1er jour du mois courant (mois courant pas compté)
     */
    private function fullMonthsFromInscription(\DateTimeImmutable $inscription, \DateTimeImmutable $now): int
    {
        $start = $inscription->modify('first day of next month')->setTime(0, 0, 0);
        $end = $now->modify('first day of this month')->setTime(0, 0, 0);

        if ($end <= $start) {
            return 0;
        }

        $diff = $start->diff($end);
        return max(0, ($diff->y * 12) + $diff->m);
    }
}
