<?php

namespace App\Command;

use App\Entity\Session;
use App\Entity\StudyClass;
use App\Entity\SessionStudyClassPresence;
use App\Repository\StudyClassRepository;
use App\Repository\StudentClassRegisteredRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:sessions:generate-week',
    description: 'Génère des sessions pour la semaine (ou un jour précis) à partir des StudyClass planifiées'
)]
class GenerateWeeklySessionsCommand extends Command
{
    private const DAY_MAP = [
        StudyClass::DAY_MONDAY    => 'monday',
        StudyClass::DAY_TUESDAY   => 'tuesday',
        StudyClass::DAY_WEDNESDAY => 'wednesday',
        StudyClass::DAY_THURSDAY  => 'thursday',
        StudyClass::DAY_FRIDAY    => 'friday',
        StudyClass::DAY_SATURDAY  => 'saturday',
        StudyClass::DAY_SUNDAY    => 'sunday',
    ];

    // Normalisation des entrées pour --only-day
    private const FR_INPUT_TO_EN = [
        'lundi'    => 'monday',
        'mardi'    => 'tuesday',
        'mercredi' => 'wednesday',
        'jeudi'    => 'thursday',
        'vendredi' => 'friday',
        'samedi'   => 'saturday',
        'dimanche' => 'sunday',
        '1'        => 'monday',
        '2'        => 'tuesday',
        '3'        => 'wednesday',
        '4'        => 'thursday',
        '5'        => 'friday',
        '6'        => 'saturday',
        '7'        => 'sunday',
    ];

    // Normalisation des entrées pour --class-type
    // (clé = saisie CLI ; valeur = constante StudyClass correspondante)
    private const CLASS_TYPE_MAP = [
        'arabe'   => StudyClass::CLASS_TYPE_ARABE,
        'soutien' => StudyClass::CLASS_TYPE_SOUTIEN,
        'autre'   => StudyClass::CLASS_TYPE_AUTRE,
        'all'     => null, // pas de filtre
    ];

    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly StudyClassRepository $studyClassRepo,
        private readonly StudentClassRegisteredRepository $studentClassRegisteredRepository,
        private readonly string $timezone = 'Europe/Paris',
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption(
            'week-start',
            null,
            InputOption::VALUE_OPTIONAL,
            'Date du lundi de la semaine ciblée (YYYY-MM-DD). Par défaut : semaine courante.'
        );
        $this->addOption(
            'for-date',
            null,
            InputOption::VALUE_OPTIONAL,
            'Fixe la semaine à partir de cette date (YYYY-MM-DD). Utile pour "la semaine du JJ/MM/AAAA".'
        );
        $this->addOption(
            'only-day',
            null,
            InputOption::VALUE_OPTIONAL,
            'Générer uniquement pour ce jour (lundi..dimanche ou 1..7; 1=lundi, 7=dimanche).'
        );
        $this->addOption(
            'strict-school-year',
            null,
            InputOption::VALUE_NONE,
            'N\'ajouter que les sessions si la date tombe dans l\'année scolaire de la StudyClass (ex: 2024/2025 = 01/09/2024 → 31/08/2025).'
        );
        // >>> NOUVEAU : filtre par type de classe
        $this->addOption(
            'class-type',
            null,
            InputOption::VALUE_OPTIONAL,
            'Filtrer par type de classe: arabe | soutien | autre | all. Plusieurs possibles séparées par virgule (ex: "arabe,soutien"). Défaut: all.',
            'all'
        );
        // (Optionnel) si tu veux rendre l’année paramétrable, dé-commente :
        // $this->addOption('school-year', null, InputOption::VALUE_OPTIONAL, 'Année scolaire (ex: 2025/2026).', StudyClass::SCHOOL_YEAR_2025_2026);
    }

    protected function execute(InputInterface $input, \Symfony\Component\Console\Output\OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $tz = new \DateTimeZone($this->timezone);

        $strictSchoolYear = (bool)$input->getOption('strict-school-year');

        // Détermine la semaine cible
        $forDateOpt = $input->getOption('for-date');
        if ($forDateOpt) {
            $refDate  = new \DateTimeImmutable($forDateOpt, $tz);
            $weekStart = $refDate->modify('monday this week')->setTime(0, 0);
        } else {
            $weekStartOpt = $input->getOption('week-start');
            if ($weekStartOpt) {
                $weekStart = (new \DateTimeImmutable($weekStartOpt, $tz))->modify('monday this week')->setTime(0, 0);
            } else {
                $weekStart = (new \DateTimeImmutable('now', $tz))->modify('monday this week')->setTime(0, 0);
            }
        }
        $weekEnd = $weekStart->modify('sunday this week')->setTime(23, 59, 59);

        // Normalise --only-day
        $onlyDayOpt = $input->getOption('only-day');
        $onlyDayKey = null;
        if ($onlyDayOpt) {
            $key = mb_strtolower(trim((string)$onlyDayOpt));
            $onlyDayKey = self::FR_INPUT_TO_EN[$key] ?? null;
            if (!$onlyDayKey) {
                $io->error('Valeur invalide pour --only-day. Attendu : lundi..dimanche ou 1..7.');
                return Command::INVALID;
            }
        }

        // Normalise --class-type (supporte plusieurs valeurs séparées par virgule)
        $classTypeOpt = (string)$input->getOption('class-type');
        $classTypeKeys = array_filter(array_map(
            fn ($x) => mb_strtolower(trim($x)),
            explode(',', $classTypeOpt)
        ));
        if (empty($classTypeKeys)) {
            $classTypeKeys = ['all'];
        }
        // Valide les valeurs
        foreach ($classTypeKeys as $k) {
            if (!array_key_exists($k, self::CLASS_TYPE_MAP)) {
                $io->error('Valeur invalide pour --class-type. Accepté: arabe | soutien | autre | all (ou plusieurs séparées par virgule).');
                return Command::INVALID;
            }
        }
        // Traduit en valeurs de DB (constantes)
        $normalizedTypes = array_values(array_unique(array_filter(
            array_map(fn ($k) => self::CLASS_TYPE_MAP[$k], $classTypeKeys),
            fn ($v) => $v !== null // enlève "all"
        )));

        $io->section(sprintf(
            'Semaine ciblée : %s → %s%s | Types: %s',
            $weekStart->format('Y-m-d'),
            $weekEnd->format('Y-m-d'),
            $onlyDayKey ? " | Jour ciblé : $onlyDayKey" : '',
            $normalizedTypes ? implode(', ', $normalizedTypes) : 'tous'
        ));

        // === Récupération des classes selon filtres ===
        $schoolYear = StudyClass::SCHOOL_YEAR_2025_2026; // ou $input->getOption('school-year');

        if ($normalizedTypes && count($normalizedTypes) > 1) {
            // Plusieurs types -> QueryBuilder avec IN()
            $qb = $this->studyClassRepo->createQueryBuilder('sc')
                ->where('sc.schoolYear = :year')
                ->andWhere('sc.classType IN (:types)')
                ->setParameter('year', $schoolYear)
                ->setParameter('types', $normalizedTypes);
            /** @var StudyClass[] $classes */
            $classes = $qb->getQuery()->getResult();
        } elseif ($normalizedTypes && count($normalizedTypes) === 1) {
            // Un seul type -> findBy simple
            $classes = $this->studyClassRepo->findBy([
                'schoolYear' => $schoolYear,
                'classType'  => $normalizedTypes[0],
            ]);
        } else {
            // "all" -> pas de filtre classType
            $classes = $this->studyClassRepo->findBy([
                'schoolYear' => $schoolYear,
            ]);
        }

        $created = 0;
        $skipped = 0;

        foreach ($classes as $class) {
            $dayLabel = $class->getDay();
            $startHour = $class->getStartHour();
            $endHour   = $class->getEndHour();

            if (!$dayLabel || !$startHour || !$endHour) {
                $skipped++;
                continue;
            }

            $dayKey = self::DAY_MAP[$dayLabel] ?? null;
            if (!$dayKey) {
                $skipped++;
                continue;
            }

            // Si --only-day est fourni, ignorer les autres jours
            if ($onlyDayKey && $dayKey !== $onlyDayKey) {
                $skipped++;
                continue;
            }

            // Date exacte du cours dans la semaine
            $sessionDate = $weekStart->modify($dayKey . ' this week');
            $startTime = $sessionDate->setTime((int)$startHour->format('H'), (int)$startHour->format('i'), 0);
            $endTime   = $sessionDate->setTime((int)$endHour->format('H'), (int)$endHour->format('i'), 0);

            // Respect éventuel de l'année scolaire
            if ($strictSchoolYear && !$this->isInSchoolYear($startTime, (string)$class->getSchoolYear(), $tz)) {
                $skipped++;
                continue;
            }

            // Anti-doublon : même classe + même startTime
            $exists = $this->em->getRepository(Session::class)->createQueryBuilder('s')
                ->select('COUNT(s.id)')
                ->where('s.studyClass = :cls')
                ->andWhere('s.startTime = :start')
                ->setParameter('cls', $class)
                ->setParameter('start', $startTime)
                ->getQuery()
                ->getSingleScalarResult();

            if ((int)$exists > 0) {
                $skipped++;
                continue;
            }

            // Salle & prof par défaut depuis StudyClass
            $room = $class->getPrincipalRoom();
            $teacher = $class->getPrincipalTeacher();

            // Création de la session
            $session = (new Session())
                ->setStartTime($startTime)
                ->setEndTime($endTime)
                ->setStudyClass($class)
                ->setRoom($room ?? $class->getPrincipalRoom())
                ->setTeacher($teacher ?? $class->getPrincipalTeacher());

            $this->em->persist($session);

            // Présences
            $allStudents = $this->studentClassRegisteredRepository->findStudentsActiveInStudyClass($class);
            foreach ($allStudents as $student) {
                $presence = new SessionStudyClassPresence($student->getStudent(), $session);
                $this->em->persist($presence);
            }

            $created++;
        }

        $this->em->flush();

        $io->success(sprintf('Sessions créées : %d | ignorées : %d', $created, $skipped));
        return Command::SUCCESS;
    }

    /**
     * Parse "YYYY/YYYY+1" en [01/09/YYYY ; 31/08/YYYY+1].
     */
    private function isInSchoolYear(\DateTimeImmutable $date, string $schoolYear, \DateTimeZone $tz): bool
    {
        if (!preg_match('#^(\d{4})/(\d{4})$#', $schoolYear, $m)) {
            return true; // si format inconnu, on ne bloque pas
        }
        $y1 = (int)$m[1];
        $y2 = (int)$m[2];

        $start = new \DateTimeImmutable("$y1-09-01 00:00:00", $tz);
        $end   = new \DateTimeImmutable("$y2-08-31 23:59:59", $tz);

        return $date >= $start && $date <= $end;
    }
}
