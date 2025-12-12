<?php

namespace App\Service;

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

        $registrations = $this->registrationRepository->findStudentsActiveInStudyClassBySchoolYear($schoolYear);

        foreach ($registrations as $registration) {
            $student    = $registration->getStudent();
            $parent     = $student?->getParent();
            $studyClass = $registration->getStudyClass();

            if (!$parent || !$studyClass) { continue; }

            if (method_exists($registration, 'isActive') && $registration->isActive() === false) { continue; }

            $parentId  = $parent->getId();
            $studentId = $student->getId();

            if (!isset($parents[$parentId])) {
                $parents[$parentId] = [
                    'parentId'                  => $parentId,
                    'parentName'                => $this->formatParentName($parent),
                    'email'                     => $this->guessEmail($parent),
                    'phone'                     => $this->guessPhone($parent),

                    // --- Compteurs Dettes ---
                    'arabChildrenIds'           => [],
                    'arabChildrenCount'         => 0,
                    'soutienRegistrationsCount' => 0,
                    'dueArabe'                  => 0.0,
                    'dueSoutien'                => 0.0,

                    // --- Compteurs Paiements (NOUVEAU) ---
                    'paidArabe'                 => 0.0,
                    'paidSoutien'               => 0.0,

                    // --- Totaux ---
                    'totalDue'                  => 0.0,
                    'totalPaid'                 => 0.0,
                    'remaining'                 => 0.0,
                ];
            }

            $type = $studyClass->getClassType();

            if ($type === StudyClass::CLASS_TYPE_SOUTIEN) {
                $parents[$parentId]['soutienRegistrationsCount']++;
            }
            if ($type === StudyClass::CLASS_TYPE_ARABE) {
                $parents[$parentId]['arabChildrenIds'][$studentId] = true;
            }
        }

        foreach ($parents as $parentId => &$data) {
            // Arabe
            $countChildrenArabe = \count($data['arabChildrenIds']);
            $data['arabChildrenCount'] = $countChildrenArabe;

            $data['dueArabe'] = match (true) {
                $countChildrenArabe === 1 => 240,
                $countChildrenArabe === 2 => 450,
                $countChildrenArabe === 3 => 630,
                $countChildrenArabe >= 4  => 200 * $countChildrenArabe,
                default => 0,
            };

            // Soutien
            $data['dueSoutien'] = $data['soutienRegistrationsCount'] * 75;

            // Total
            $data['totalDue'] = $data['dueArabe'] + $data['dueSoutien'];
        }
        unset($data);


        $payments = $this->paymentRepository->findAllPayementsForSchoolYear($schoolYear);

        foreach ($payments as $payment) {
            $parent = $payment->getParent();
            if (!$parent || !isset($parents[$parent->getId()])) {
                continue;
            }

            $parentId = $parent->getId();
            $amount   = (float) $payment->getAmountPaid();


            $serviceType = (string) $payment->getServiceType();

            if (stripos($serviceType, 'soutien') !== false) {
                $parents[$parentId]['paidSoutien'] += $amount;
            } else {
                $parents[$parentId]['paidArabe'] += $amount;
            }

            $parents[$parentId]['totalPaid'] += $amount;
        }

        $totalDueAll       = 0.0;
        $totalPaidAll      = 0.0;
        $totalRemainingAll = 0.0;
        $rows              = [];

        foreach ($parents as $parentId => $data) {
            $remaining = $data['totalDue'] - $data['totalPaid'];

            if ($remaining <= 0.01) { continue; }

            $data['remaining'] = $remaining;

            unset($data['arabChildrenIds']);

            $totalDueAll       += $data['totalDue'];
            $totalPaidAll      += $data['totalPaid'];
            $totalRemainingAll += $data['remaining'];

            $rows[] = $data;
        }

        \usort($rows, static fn (array $a, array $b) => $b['remaining'] <=> $a['remaining']);

        return [
            'schoolYear' => $schoolYear,
            'parents'    => $rows,
            'totals'     => [
                'parentsCount'   => \count($rows),
                'totalDue'       => round($totalDueAll, 2),
                'totalPaid'      => round($totalPaidAll, 2),
                'totalRemaining' => round($totalRemainingAll, 2),
            ],
        ];
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
