<?php
// src/Command/SendRegistrationConfirmationToParentsCommand.php

namespace App\Command;

use App\Entity\RegistrationArabicCours;
use App\Entity\StudentClassRegistered;
use App\Entity\StudyClass;
use App\Repository\RegistrationArabicCoursRepository;
use App\Repository\StudentClassRegisteredRepository;
use App\Repository\StudyClassRepository;
use App\Service\MailService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:send-registration-confirmations',
    description: 'Envoie l’email de confirmation et passe l’inscription à STEP_COMPTE_CREATION pour les cours d’Arabe.'
)]
class SendRegistrationConfirmationToParentsCommand extends Command
{
    public function __construct(
        private StudyClassRepository $studyClassRepository,
        private StudentClassRegisteredRepository $classRegisteredRepository,
        private RegistrationArabicCoursRepository $registrationArabicCoursRepository,
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
                'class-ids',
                null,
                InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY,
                'IDs des classes (StudyClass.id) à traiter (en plus du filtre). Répéter l’option ou CSV (ex: --class-ids=12,34).'
            )
            // NEW: jour ciblé (par défaut mercredi)
            ->addOption(
                'day',
                null,
                InputOption::VALUE_REQUIRED,
                'Jour ciblé. Accepte les constantes (DAY_WEDNESDAY), les noms (mercredi/wednesday) ou un numéro (1-7).',
                'wednesday'
            );
    }

    protected function execute(InputInterface $input, \Symfony\Component\Console\Output\OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $yearLabel = (string) $input->getOption('year-label');
        $dryRun = (bool) $input->getOption('dry-run');

        // --- helper local pour parser l’option --day
        $parseDay = function (string $dayOpt) use ($io) {
            $raw = trim($dayOpt);

            // 1) Constante DAY_*
            if (str_starts_with(strtoupper($raw), 'DAY_')) {
                $const = StudyClass::class.'::'.strtoupper($raw);
                if (\defined($const)) {
                    return \constant($const);
                }
                $io->warning(sprintf('Constante de jour inconnue: %s', $raw));
            }

            // 2) Numérique 1..7
            if (ctype_digit($raw)) {
                $n = (int) $raw;
                if ($n >= 1 && $n <= 7) {
                    return $n;
                }
                $io->warning('Jour numérique hors plage (1..7).');
            }

            // 3) Noms FR/EN -> constantes si disponibles, sinon chiffre 1..7 si votre modèle stocke ainsi
            $map = [
                'LUNDI'     => 'DAY_MONDAY',
                'MARDI'     => 'DAY_TUESDAY',
                'MERCREDI'  => 'DAY_WEDNESDAY',
                'JEUDI'     => 'DAY_THURSDAY',
                'VENDREDI'  => 'DAY_FRIDAY',
                'SAMEDI'    => 'DAY_SATURDAY',
                'DIMANCHE'  => 'DAY_SUNDAY',
                'MONDAY'    => 'DAY_MONDAY',
                'TUESDAY'   => 'DAY_TUESDAY',
                'WEDNESDAY' => 'DAY_WEDNESDAY',
                'THURSDAY'  => 'DAY_THURSDAY',
                'FRIDAY'    => 'DAY_FRIDAY',
                'SATURDAY'  => 'DAY_SATURDAY',
                'SUNDAY'    => 'DAY_SUNDAY',
            ];
            $upper = strtoupper($raw);
            if (isset($map[$upper])) {
                $const = StudyClass::class.'::'.$map[$upper];
                if (\defined($const)) {
                    return \constant($const);
                }
            }

            // Fallback: mercredi
            $io->note('Jour invalide, fallback sur mercredi.');
            return \defined(StudyClass::class.'::DAY_WEDNESDAY')
                ? \constant(StudyClass::class.'::DAY_WEDNESDAY')
                : 3; // ajustez si votre modèle stocke 1..7
        };

        $io->title('Confirmation d’inscription – ' . $yearLabel);

        // -- options
        $dayOpt = (string) ($input->getOption('day') ?? 'wednesday');
        $targetDay = $parseDay($dayOpt);

        /** @var array<int|string> $rawIds */
        $rawIds = (array) ($input->getOption('class-ids') ?? []);
        $ids = [];
        foreach ($rawIds as $chunk) {
            foreach (preg_split('/\s*,\s*/', (string) $chunk, -1, PREG_SPLIT_NO_EMPTY) ?: [] as $idStr) {
                if (ctype_digit($idStr)) {
                    $ids[] = (int) $idStr;
                } else {
                    $io->warning(sprintf('ID de classe invalide ignoré: "%s"', $idStr));
                }
            }
        }
        $ids = array_values(array_unique($ids));

        // --- sélection des classes: spécialité Arabe + jour filtré (+ IDs si fournis)
        if (!empty($ids)) {
            $studyClasses = [];
            foreach ($ids as $id) {
                /** @var StudyClass|null $cls */
                $cls = $this->studyClassRepository->find($id);
                if (!$cls) {
                    $io->warning(sprintf('StudyClass #%d introuvable — ignorée.', $id));
                    continue;
                }
                if ($cls->getSpeciality() !== StudyClass::CLASS_TYPE_ARABE) {
                    $io->note(sprintf('Classe #%d "%s" non-Arabe — ignorée.', $id, $cls->getName()));
                    continue;
                }
                if ($cls->getDay() != $targetDay) { // ==/!= pour tolérer int vs string
                    $io->note(sprintf(
                        'Classe #%d "%s" jour=%s ≠ cible — ignorée.',
                        $id, $cls->getName(), (string) $cls->getDay()
                    ));
                    continue;
                }
                $studyClasses[] = $cls;
            }
        } else {
            // Repo direct si aucun ID fourni
            $studyClasses = $this->studyClassRepository->findBy([
                'speciality' => StudyClass::CLASS_TYPE_ARABE,
                'day'        => $targetDay,
            ]);
        }

        if (!$studyClasses) {
            $io->warning(sprintf('Aucune classe Arabe pour le jour demandé (%s).', $dayOpt));
            return Command::SUCCESS;
        }

        $sentCount = 0;
        $skipped = 0;
        $advanced = 0;

        foreach ($studyClasses as $studyClass) {
            /** @var StudyClass $studyClass */

            // Inscriptions actives par classe
            $registrations = $this->classRegisteredRepository->findBy([
                'studyClass' => $studyClass,
                'active'     => true,
            ]);

            foreach ($registrations as $registration) {
                /** @var StudentClassRegistered $registration */
                $student = $registration->getStudent();
                $parent  = $student?->getParent();

                if (!$student || !$parent) {
                    $skipped++;
                    continue;
                }

                // (Option) vrai filtre “payé”
                // $hasPaid = $this->paymentRepository->hasPaidFor($student, $studyClass, $yearLabel);
                $hasPaid = true;

                if (!$hasPaid) {
                    $io->note(sprintf(
                        'Paiement non confirmé pour %s (%s), classe %s — e-mail non envoyé.',
                        $student->getFullName(),
                        $yearLabel,
                        $studyClass->getName()
                    ));
                    $skipped++;
                    continue;
                }

                // Email
                $emailTo = $parent->getEmailContact();
                if (!$emailTo) {
                    $io->warning(sprintf('Pas d’email parent pour %s — ignoré.', $student->getFullName()));
                    $skipped++;
                    continue;
                }

                $context = [
                    'yearLabel'       => $yearLabel,
                    'parentFullName'  => $parent->getFullNameParent(),
                    'studentFullName' => $student->getFullName(),
                    'studentDob'      => $student->getBirthDate(),
                    'className'       => $studyClass->getName(),
                    'classLevel'      => $studyClass->getLevelClass(),
                    'speciality'      => $studyClass->getSpeciality(),
                    'day'             => $studyClass->getDay(),
                    'startHour'       => $studyClass->getStartHour(),
                    'endHour'         => $studyClass->getEndHour(),
                ];

                $subject = sprintf(
                    'Confirmation d’inscription %s – %s (%s)',
                    $yearLabel,
                    $student->getFullName(),
                    $studyClass->getName()
                );

                if ($dryRun) {
                    $io->text(sprintf('[DRY RUN] À: %s | Sujet: %s', $emailTo, $subject));
                } else {
                    $this->mailService->sendEmail(
                        to: $emailTo,
                        subject: $subject,
                        template: 'email/registration_enrollment_confirmation.html.twig',
                        context: $context,
                        sender: 'contact@ccib38.fr'
                    );
                }
                $sentCount++;

                // Avancer l’étape de RegistrationArabicCours -> STEP_COMPTE_CREATION
                $rac = $this->registrationArabicCoursRepository->findBy(
                    ['student' => $student],
                    ['createdAt' => 'DESC'],
                    1
                );


                /** @var RegistrationArabicCours|null $latest */
                $latest = $rac[0] ?? null;
                if ($latest->getStepRegistration() === RegistrationArabicCours::STEP_LIST_WAITING) {
                    $io->warning(sprintf(
                        'RegistrationArabicCours pour %s a stepRegistration NULL — vérifier manuellement.',
                        $student->getFullName()
                    ));
                }
                if ($latest) {
                    if ($latest->getStepRegistration() !== RegistrationArabicCours::STEP_COMPTE_CREATION) {
                        $latest->setStepRegistration(RegistrationArabicCours::STEP_COMPTE_CREATION);
                        if (!$dryRun) {
                            $this->entityManager->persist($latest);
                        }
                        $advanced++;
                    }
                } else {
                    $io->note(sprintf(
                        'Aucune RegistrationArabicCours liée trouvée pour %s — étape non mise à jour.',
                        $student->getFullName()
                    ));
                }
            }
        }

        if (!$dryRun) {
            $this->entityManager->flush();
        }

        $io->success(sprintf(
            'Terminé. E-mails envoyés: %d | Étapes avancées: %d | Ignorés: %d%s',
            $sentCount,
            $advanced,
            $skipped,
            $dryRun ? ' (dry-run)' : ''
        ));

        return Command::SUCCESS;
    }


    /**
     * Convertit l’option --day en valeur attendue par StudyClass::getDay()/DB.
     * Accepte:
     *  - Noms FR/EN: lundi..dimanche / monday..sunday
     *  - Constantes: DAY_WEDNESDAY
     *  - Numérique: 1..7
     */
    private function parseDayOption(string $dayOpt, SymfonyStyle $io): int|string
    {
        $raw = trim($dayOpt);

        // 1) Constante DAY_*
        if (str_starts_with(strtoupper($raw), 'DAY_')) {
            $const = StudyClass::class.'::'.strtoupper($raw);
            if (\defined($const)) {
                /** @var int|string $val */
                $val = \constant($const);
                return $val;
            }
            $io->warning(sprintf('Constante de jour inconnue: %s', $raw));
        }

        // 2) Numérique
        if (ctype_digit($raw)) {
            $n = (int) $raw; // ex: 1=lundi/mon.. selon ton modèle
            if ($n >= 1 && $n <= 7) {
                return $n;
            }
            $io->warning('Jour numérique hors plage (1..7).');
        }

        // 3) Noms FR/EN -> constantes si dispo, sinon fallback num.
        $map = [
            'LUNDI'     => 'DAY_MONDAY',
            'MARDI'     => 'DAY_TUESDAY',
            'MERCREDI'  => 'DAY_WEDNESDAY',
            'JEUDI'     => 'DAY_THURSDAY',
            'VENDREDI'  => 'DAY_FRIDAY',
            'SAMEDI'    => 'DAY_SATURDAY',
            'DIMANCHE'  => 'DAY_SUNDAY',
            'MONDAY'    => 'DAY_MONDAY',
            'TUESDAY'   => 'DAY_TUESDAY',
            'WEDNESDAY' => 'DAY_WEDNESDAY',
            'THURSDAY'  => 'DAY_THURSDAY',
            'FRIDAY'    => 'DAY_FRIDAY',
            'SATURDAY'  => 'DAY_SATURDAY',
            'SUNDAY'    => 'DAY_SUNDAY',
            // si ton modèle stocke 1..7, tu peux plutôt retourner 1..7 ici.
        ];
        $upper = strtoupper($raw);
        if (isset($map[$upper])) {
            $const = StudyClass::class.'::'.$map[$upper];
            if (\defined($const)) {
                /** @var int|string $val */
                $val = \constant($const);
                return $val;
            }
        }

        // Fallback par défaut: mercredi
        $io->note('Jour invalide, fallback sur mercredi.');
        /** @var int|string $wed */
        $wed = \defined(StudyClass::class.'::DAY_WEDNESDAY')
            ? \constant(StudyClass::class.'::DAY_WEDNESDAY')
            : 3; // adapte si ton modèle utilise 1..7
        return $wed;
    }

}
