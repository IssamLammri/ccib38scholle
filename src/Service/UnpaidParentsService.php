<?php

namespace App\Service;

use App\Entity\StudentClassRegistered;
use App\Entity\StudyClass;
use App\Entity\ParentEntity;
use App\Repository\StudentClassRegisteredRepository;
use App\Repository\PaymentRepository;

class UnpaidParentsService
{
    public function __construct(
        private StudentClassRegisteredRepository $registrationRepository,
        private PaymentRepository $paymentRepository,
    ) {
    }

    /**
     * Calcule, pour une année donnée, les montants dus / payés / restants par parent.
     */
    public function computeUnpaidParents(string $schoolYear): array
    {
        $parents = [];

        // On garde cette requête juste pour trouver les familles concernées (ayant au moins 1 inscription active)
        $registrations = $this->registrationRepository
            ->findStudentsActiveInStudyClassBySchoolYear($schoolYear);

        /** @var StudentClassRegistered $registration */
        foreach ($registrations as $registration) {
            if ($registration->isActive() === false) {
                continue;
            }

            $student = $registration->getStudent();
            $parent  = $student?->getParent();

            if (!$parent) {
                continue;
            }

            $parentId = $parent->getId();
            if (!$parentId) {
                continue;
            }

            if (!isset($parents[$parentId])) {
                // DÛ vient uniquement de la DB, arrondi à l’euro près
                $dueArabe   = (int) round((float) $parent->getAmountDueArabic());
                $dueSoutien = (int) round((float) $parent->getAmountDueSoutien());

                $parents[$parentId] = [
                    'parentId'   => $parentId,
                    'parentName' => $this->formatParentName($parent),
                    'email'      => $this->guessEmail($parent),
                    'phone'      => $this->guessPhone($parent),

                    // optionnel si le front les affiche
                    'arabChildrenCount'         => 0,
                    'soutienRegistrationsCount' => 0,

                    // DÛ DB sans virgule
                    'dueArabe'   => $dueArabe,
                    'dueSoutien' => $dueSoutien,

                    // Paiements
                    'paidArabe'   => 0,
                    'paidSoutien' => 0,

                    // Totaux
                    'totalDue'  => $dueArabe + $dueSoutien,
                    'totalPaid' => 0,
                    'remaining' => 0,
                ];
            }
        }

        // Paiements : inchangé, mais arrondis aussi à l’euro
        $payments = $this->paymentRepository->findAllPayementsForSchoolYear($schoolYear);

        foreach ($payments as $payment) {
            $parent = $payment->getParent();
            if (!$parent) {
                continue;
            }

            $parentId = $parent->getId();
            if (!$parentId || !isset($parents[$parentId])) {
                continue;
            }

            $amount = (int) round((float) $payment->getAmountPaid());
            $serviceType = (string) $payment->getServiceType();

            // Sécurise studyClass
            $paymentStudyClass = $payment->getStudyClass();
            if ($paymentStudyClass && (string) $paymentStudyClass->getSchoolYear() !== $schoolYear) {
                continue; // on ignore les paiements hors année
            }

            if (stripos($serviceType, 'soutien') !== false) {
                $parents[$parentId]['paidSoutien'] += $amount;
            } else {
                $parents[$parentId]['paidArabe'] += $amount;
            }

            $parents[$parentId]['totalPaid'] += $amount;
        }

        // Totaux & reste
        $totalDueAll       = 0;
        $totalPaidAll      = 0;
        $totalRemainingAll = 0;
        $rows              = [];

        foreach ($parents as $parentId => $data) {
            // Recalcul total dû
            $data['totalDue'] = (int) $data['dueArabe'] + (int) $data['dueSoutien'];

            $remaining = (int) round($data['totalDue'] - (int) $data['totalPaid']);

            // on garde uniquement ceux qui doivent encore
            if ($remaining <= 0) {
                continue;
            }

            $data['remaining'] = $remaining;

            $totalDueAll       += $data['totalDue'];
            $totalPaidAll      += $data['totalPaid'];
            $totalRemainingAll += $data['remaining'];

            $rows[] = $data;
        }

        usort($rows, static fn (array $a, array $b) => $b['remaining'] <=> $a['remaining']);

        return [
            'schoolYear' => $schoolYear,
            'parents'    => $rows,
            'totals'     => [
                'parentsCount'   => count($rows),
                'totalDue'       => (int) round($totalDueAll),
                'totalPaid'      => (int) round($totalPaidAll),
                'totalRemaining' => (int) round($totalRemainingAll),
            ],
        ];
    }

    private function getNumbreOfWeekBetween(\DateTimeImmutable $registrationDate): int
    {
        $now = new \DateTimeImmutable('now');

        // Diff absolue (peu importe si la date est dans le futur)
        $interval = $registrationDate->diff($now, true);

        // Jours totaux -> semaines entières
        $days = $interval->days ?? 0;

        return intdiv($days, 7);
    }


    private function getNumberOfMonth(\DateTimeImmutable $registrationDate): int
    {
        $weeks = $this->getNumbreOfWeekBetween($registrationDate);

        if ($weeks <= 4) {
            return 1;
        }

        // 5..8 => 2, 9..12 => 3, 13..16 => 4, ...
        return intdiv($weeks - 5, 4) + 2;
    }


    private function formatParentName(ParentEntity $parent): string
    {
        $father = trim(($parent->getFatherFirstName() ?? '') . ' ' . ($parent->getFatherLastName() ?? ''));
        $mother = trim(($parent->getMotherFirstName() ?? '') . ' ' . ($parent->getMotherLastName() ?? ''));

        if ($father && $mother) {
            return sprintf('%s / %s', $father, $mother);
        }

        return $father ?: $mother ?: 'Parent sans nom';
    }

    private function guessEmail(ParentEntity $parent): ?string
    {
        if ($parent->getFatherEmail()) {
            return $parent->getFatherEmail();
        }
        if ($parent->getMotherEmail()) {
            return $parent->getMotherEmail();
        }

        return null;
    }

    private function guessPhone(ParentEntity $parent): ?string
    {
        if ($parent->getFatherPhone()) {
            return $parent->getFatherPhone();
        }
        if ($parent->getMotherPhone()) {
            return $parent->getMotherPhone();
        }

        return null;
    }
}
