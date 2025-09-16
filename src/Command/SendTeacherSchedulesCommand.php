<?php
// src/Command/SendTeacherSchedulesCommand.php

namespace App\Command;

use App\Entity\StudyClass;
use App\Entity\Teacher;
use App\Repository\TeacherRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Service\MailService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:send-teacher-schedules',
    description: 'Envoie aux enseignants le récapitulatif de leurs classes (jour + horaire + niveau + spécialité).'
)]
class SendTeacherSchedulesCommand extends Command
{
    public function __construct(
        private TeacherRepository $teacherRepository,
        private EntityManagerInterface $entityManager,
        private MailService $mailService,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('year-label', null, InputOption::VALUE_REQUIRED, 'Libellé de l’année scolaire à afficher', '2025/2026')
            ->addOption('dry-run', null, InputOption::VALUE_NONE, 'N’envoie pas d’e-mail, affiche seulement ce qui serait envoyé')
            ->addOption(
                'teacher-ids',
                null,
                InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY,
                'IDs des enseignants à cibler (Teacher.id). Répéter l’option ou CSV (ex: --teacher-ids=2,5).'
            )
            ->addOption(
                'day',
                null,
                InputOption::VALUE_REQUIRED,
                'Filtrer par jour (noms FR/EN: lundi..dimanche / monday..sunday, ou constantes DAY_*).'
            )
            ->addOption(
                'speciality',
                null,
                InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY,
                'Filtrer par spécialité (ex: Arabe, "Soutien scolaire"). Répéter l’option ou CSV. Laisser vide = toutes.'
            );
    }

    protected function execute(InputInterface $input, \Symfony\Component\Console\Output\OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Envoi des emplois du temps aux enseignants');

        $yearLabel = (string) $input->getOption('year-label');
        $dryRun    = (bool)   $input->getOption('dry-run');

        // -- teacher IDs (optionnel)
        /** @var array<int|string> $rawTeacherIds */
        $rawTeacherIds = (array) ($input->getOption('teacher-ids') ?? []);
        $teacherIds = [];
        foreach ($rawTeacherIds as $chunk) {
            foreach (preg_split('/\s*,\s*/', (string) $chunk, -1, PREG_SPLIT_NO_EMPTY) ?: [] as $idStr) {
                if (ctype_digit($idStr)) {
                    $teacherIds[] = (int) $idStr;
                } else {
                    $io->warning(sprintf('ID enseignant invalide ignoré: "%s"', $idStr));
                }
            }
        }
        $teacherIds = array_values(array_unique($teacherIds));

        // -- day (optionnel)
        $dayOpt = $input->getOption('day');
        $targetDay = null;
        if (is_string($dayOpt) && $dayOpt !== '') {
            $targetDay = $this->parseDayOption($dayOpt, $io); // renvoie une chaîne type 'Mercredi'
            $io->note(sprintf('Filtre jour: %s', $targetDay));
        }

        // -- specialities (optionnel, plusieurs)
        /** @var array<int|string> $rawSpecs */
        $rawSpecs = (array) ($input->getOption('speciality') ?? []);
        $specialities = [];
        foreach ($rawSpecs as $chunk) {
            foreach (preg_split('/\s*,\s*/', (string) $chunk, -1, PREG_SPLIT_NO_EMPTY) ?: [] as $s) {
                $specialities[] = $s;
            }
        }
        $specialities = array_values(array_unique($specialities));
        if ($specialities) {
            $io->note('Filtre spécialités: '.implode(', ', $specialities));
        }

        // -- récupération des enseignants
        if (!empty($teacherIds)) {
            $teachers = [];
            foreach ($teacherIds as $id) {
                $t = $this->teacherRepository->find($id);
                if (!$t) {
                    $io->warning(sprintf('Teacher #%d introuvable — ignoré.', $id));
                    continue;
                }
                $teachers[] = $t;
            }
        } else {
            $teachers = $this->teacherRepository->findAll();
        }

        if (!$teachers) {
            $io->warning('Aucun enseignant trouvé pour les critères donnés.');
            return Command::SUCCESS;
        }

        $emailedTeachers = 0;
        $skippedNoEmail  = 0;
        $skippedNoClasses = 0;

        foreach ($teachers as $teacher) {
            /** @var Teacher $teacher */
            $email = $teacher->getEmail();
            if (!$email) {
                $io->warning(sprintf('Enseignant %s %s sans email — ignoré.',
                    $teacher->getFirstName() ?? '',
                    $teacher->getLastName() ?? ''
                ));
                $skippedNoEmail++;
                continue;
            }

            // Construire la liste des classes de ce prof, avec filtres
            $classesData = [];
            foreach ($teacher->getClasses() as $class) {
                /** @var StudyClass $class */
                // Filtre spécialité
                if ($specialities && !in_array((string) $class->getSpeciality(), $specialities, true)) {
                    continue;
                }
                // Filtre jour
                if ($targetDay !== null && $class->getDay() !== $targetDay) {
                    continue;
                }

                $classesData[] = [
                    'name'       => $class->getName(),
                    'level'      => $class->getLevelClass(),
                    'speciality' => $class->getSpeciality(),
                    'day'        => $class->getDay(),
                    'start'      => $class->getStartHour()?->format('H:i'),
                    'end'        => $class->getEndHour()?->format('H:i'),
                ];
            }

            if (!$classesData) {
                $io->note(sprintf(
                    'Aucune classe (après filtres) pour %s %s — aucun mail.',
                    $teacher->getFirstName() ?? '',
                    $teacher->getLastName() ?? ''
                ));
                $skippedNoClasses++;
                continue;
            }

            // Contexte email
            $context = [
                'yearLabel'     => $yearLabel,
                'teacherName'   => trim(($teacher->getFirstName() ?? '').' '.($teacher->getLastName() ?? '')),
                'classes'       => $classesData,
                // Optionnel: tu peux ajouter un regroupement par jour si tu veux
                // 'classesByDay' => $this->groupByDay($classesData),
            ];

            $subject = sprintf('Vos classes %s – %s', $yearLabel, $context['teacherName']);

            if ($dryRun) {
                $io->text(sprintf(
                    "[DRY RUN] À: %s | Sujet: %s | %d classe(s)",
                    $email,
                    $subject,
                    count($classesData)
                ));
            } else {
                $this->mailService->sendEmail(
                    //to: 'issamlammri5@gmail.com', // temporaire, pour tests
                    to: $email,
                    subject: $subject,
                    template: 'email/teacher_schedule_notification.html.twig',
                    context: $context,
                    sender: 'contact@ccib38.fr'
                );
            }
            $emailedTeachers++;
        }

        $io->success(sprintf(
            'Terminé. Enseignants notifiés: %d | Sans email: %d | Sans classes (après filtres): %d%s',
            $emailedTeachers,
            $skippedNoEmail,
            $skippedNoClasses,
            $dryRun ? ' (dry-run)' : ''
        ));

        return Command::SUCCESS;
    }

    /**
     * Convertit l’option --day en valeur attendue par StudyClass::getDay() (chaîne FR).
     * Accepte: noms FR/EN (lundi..dimanche / monday..sunday) et constantes DAY_*.
     */
    private function parseDayOption(string $dayOpt, SymfonyStyle $io): string
    {
        $raw = trim($dayOpt);

        // 1) Constante DAY_*
        if (str_starts_with(strtoupper($raw), 'DAY_')) {
            $mapConstToVal = [
                'DAY_MONDAY'    => StudyClass::DAY_MONDAY,
                'DAY_TUESDAY'   => StudyClass::DAY_TUESDAY,
                'DAY_WEDNESDAY' => StudyClass::DAY_WEDNESDAY,
                'DAY_THURSDAY'  => StudyClass::DAY_THURSDAY,
                'DAY_FRIDAY'    => StudyClass::DAY_FRIDAY,
                'DAY_SATURDAY'  => StudyClass::DAY_SATURDAY,
                'DAY_SUNDAY'    => StudyClass::DAY_SUNDAY,
            ];
            $upper = strtoupper($raw);
            if (isset($mapConstToVal[$upper])) {
                return $mapConstToVal[$upper];
            }
            $io->warning(sprintf('Constante de jour inconnue: %s', $raw));
        }

        // 2) Noms FR/EN -> chaîne FR telle que stockée en DB (Mercredi, etc.)
        $map = [
            'LUNDI'     => StudyClass::DAY_MONDAY,
            'MARDI'     => StudyClass::DAY_TUESDAY,
            'MERCREDI'  => StudyClass::DAY_WEDNESDAY,
            'JEUDI'     => StudyClass::DAY_THURSDAY,
            'VENDREDI'  => StudyClass::DAY_FRIDAY,
            'SAMEDI'    => StudyClass::DAY_SATURDAY,
            'DIMANCHE'  => StudyClass::DAY_SUNDAY,
            'MONDAY'    => StudyClass::DAY_MONDAY,
            'TUESDAY'   => StudyClass::DAY_TUESDAY,
            'WEDNESDAY' => StudyClass::DAY_WEDNESDAY,
            'THURSDAY'  => StudyClass::DAY_THURSDAY,
            'FRIDAY'    => StudyClass::DAY_FRIDAY,
            'SATURDAY'  => StudyClass::DAY_SATURDAY,
            'SUNDAY'    => StudyClass::DAY_SUNDAY,
        ];
        $upper = strtoupper($raw);
        if (isset($map[$upper])) {
            return $map[$upper];
        }

        $io->note('Jour invalide, aucun filtre de jour ne sera appliqué.');
        return ''; // signifie « pas de filtre »
    }

    // Optionnel si tu veux regrouper par jour pour le template
    private function groupByDay(array $classes): array
    {
        $byDay = [];
        foreach ($classes as $c) {
            $byDay[$c['day']][] = $c;
        }
        ksort($byDay);
        return $byDay;
    }
}
