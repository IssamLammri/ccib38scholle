<?php
// src/Service/ParentDashboardService.php
namespace App\Service;

use App\Entity\ParentEntity;
use App\Repository\DemandeRepository;
use App\Repository\SessionRepository;
use App\Repository\StudentClassRegisteredRepository;

final class ParentDashboardService
{
    public function __construct(
        private SessionRepository $sessionRepo,
        private StudentClassRegisteredRepository $regRepo,
        private DemandeRepository $demandeRepo,
    ) {}

    /**
     * $weekStart/$weekEnd optionnels : si non fournis, on prend la semaine courante (lun->dim)
     */
    public function buildDashboardPayload(ParentEntity $parent, ?\DateTimeImmutable $weekStart = null, ?\DateTimeImmutable $weekEnd = null): array
    {
        [$start, $end] = $this->resolveWeekRange($weekStart, $weekEnd);

        // 1) Sessions à venir (par enfant)
        $sessionRows = $this->sessionRepo->findForParentInRange($parent, $start, $end);

        $sessions = array_map(function (array $row) {
            $startAt = $row['s_start'] instanceof \DateTimeInterface ? $row['s_start'] : new \DateTimeImmutable((string)$row['s_start']);
            $endAt   = $row['s_end']   instanceof \DateTimeInterface ? $row['s_end']   : new \DateTimeImmutable((string)$row['s_end']);

            return [
                // pour affichage localisé côté front
                'start'    => $startAt->format(DATE_ATOM), // ex 2025-09-17T17:33:00+02:00
                'end'      => $endAt->format(DATE_ATOM),
                'child'    => trim(($row['st_firstName'] ?? '').' '.($row['st_lastName'] ?? '')),
                'subject'  => $row['sc_speciality'] ?? null,
                'teacher'  => trim(($row['t_lastName'] ?? '').' '.($row['t_firstName'] ?? '')),
                'room'     => $row['room_name'] ?? null,
                // présence (bool|null)
                'presence' => isset($row['presence']) ? (bool)$row['presence'] : null,
            ];
        }, $sessionRows);

        // 2) Enfants + classe principale + inscriptions
        $children = [];
        foreach ($parent->getStudents() as $student) {
            $registrations = $this->regRepo->findActiveByStudent($student);

            // “Classe principale” = la plus récente (au besoin, change cette logique)
            $main = $registrations[0] ?? null;
            $mainClass = $main?->getStudyClass();

            $children[] = [
                'id'         => $student->getId(),
                'firstName'  => $student->getFirstName(),
                'lastName'   => $student->getLastName(),
                'level'      => $student->getLevelClass(),
                'mainClass'  => $mainClass ? [
                    'label' => sprintf('%s – %s %s',
                        $mainClass->getSpeciality(),
                        $mainClass->getDay(),
                        $mainClass->getStartHour()?->format('H:i')
                    ),
                    'name'  => $mainClass->getName(),
                    'day'   => $mainClass->getDay(),
                    'time'  => ($mainClass->getStartHour()?->format('H:i')).' -> '.($mainClass->getEndHour()?->format('H:i')),
                ] : null,
                'registrations' => array_map(function($r) {
                    $sc = $r->getStudyClass();
                    return [
                        'id'    => $r->getId(),
                        'label' => sprintf('%s – %s %s',
                            $sc->getSpeciality(),
                            $sc->getDay(),
                            $sc->getStartHour()?->format('H:i')
                        ),
                        'active' => $r->isActive(),
                        'studyClass' => [
                            'id'       => $sc->getId(),
                            'name'     => $sc->getName(),
                            'level'    => $sc->getLevelClass(),
                            'day'      => $sc->getDay(),
                            'start'    => $sc->getStartHour()?->format('H:i'),
                            'end'      => $sc->getEndHour()?->format('H:i'),
                            'subject'  => $sc->getSpeciality(),
                            'teacher'  => $sc->getPrincipalTeacher()?->getLastName().' '.$sc->getPrincipalTeacher()?->getFirstName(),
                        ],
                    ];
                }, $registrations),
            ];
        }

        // 3) Historique des demandes
        $demandes = array_map(function($d){
            return [
                'id'        => $d->getId(),
                'createdAt' => $d->getCreatedAt()->format('Y-m-d H:i'),
                'status'    => $d->getStatus(), // pending/approved/rejected
                'type'      => $d->getType(),   // class_change...
                'student'   => $d->getStudent()?->getFirstName().' '.$d->getStudent()?->getLastName(),
                'level'     => $d->getStudent()?->getLevelClass(),
                'target'    => $d->getTargetStudyClass()?->getName(),
                'comment'   => $d->getComment(),
            ];
        }, $this->demandeRepo->findLatestByParent($parent, 20));

        return [
            'range'     => ['start' => $start->format('Y-m-d'), 'end' => $end->format('Y-m-d')],
            'sessions'  => $sessions,
            'children'  => $children,
            'requests'  => $demandes,
        ];
    }

    private function resolveWeekRange(?\DateTimeImmutable $start, ?\DateTimeImmutable $end): array
    {
        if ($start && $end) return [$start, $end];

        // par défaut : semaine courante (lundi 00:00 -> dimanche 23:59:59)
        $now = new \DateTimeImmutable('now');
        $monday = $now->modify('monday this week')->setTime(0,0,0);
        $sunday = $now->modify('sunday this week')->setTime(23,59,59);

        return [$monday, $sunday];
    }
}
