<?php
// src/Command/SendInvitationWhatsappToParentsCommand.php

namespace App\Command;

use App\Entity\StudentClassRegistered;
use App\Entity\StudyClass;
use App\Repository\StudentClassRegisteredRepository;
use App\Repository\StudyClassRepository;
use App\Service\MailService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:send-whatsapp-invitations',
    description: 'Envoie un email d’invitation au groupe WhatsApp à tous les parents des élèves inscrits dans les classes ciblées.'
)]
class SendInvitationWhatsappToParentsCommand extends Command
{
    public function __construct(
        private StudyClassRepository $studyClassRepository,
        private StudentClassRegisteredRepository $classRegisteredRepository,
        private MailService $mailService,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption(
                'year-label',
                null,
                InputOption::VALUE_REQUIRED,
                'Libellé de l’année scolaire à afficher dans l’email',
                '2025/2026'
            )
            ->addOption(
                'dry-run',
                null,
                InputOption::VALUE_NONE,
                'N’envoie pas d’e-mail, affiche seulement ce qui serait envoyé'
            )
            ->addOption(
                'class-ids',
                null,
                InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY,
                'IDs des classes (StudyClass.id) à traiter. Répéter l’option ou CSV (ex: --class-ids=12,34).'
            )
            ->addOption(
                'day',
                null,
                InputOption::VALUE_REQUIRED,
                'Jour ciblé SI aucune classe n’est fournie. Accepte noms (mercredi/wednesday), constantes (DAY_WEDNESDAY) ou numéro (1-7).',
                'wednesday'
            );
    }

    protected function execute(InputInterface $input, \Symfony\Component\Console\Output\OutputInterface $output): int
    {
        $io        = new SymfonyStyle($input, $output);
        $yearLabel = (string) $input->getOption('year-label');
        $dryRun    = (bool) $input->getOption('dry-run');
        $dayOpt    = (string) ($input->getOption('day') ?? 'wednesday');

        $io->title(sprintf('Envoi des invitations WhatsApp – %s', $yearLabel));

        // 1) Récupération éventuelle des IDs de classes
        /** @var array<int|string> $rawIds */
        $rawIds = (array) ($input->getOption('class-ids') ?? []);
        $ids    = [];

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

        // 2) Sélection des classes
        /** @var StudyClass[] $studyClasses */
        $studyClasses = [];

        if (!empty($ids)) {
            // Mode 1 : classes ciblées par ID, on ignore le jour
            $io->section('Sélection des classes par ID');
            foreach ($ids as $id) {
                $cls = $this->studyClassRepository->find($id);
                if (!$cls) {
                    $io->warning(sprintf('StudyClass #%d introuvable — ignorée.', $id));
                    continue;
                }
                $studyClasses[] = $cls;
                $io->text(sprintf('→ Classe #%d "%s" sélectionnée.', $cls->getId(), $cls->getName()));
            }
        } else {
            // Mode 2 : aucune ID → filtrage par jour
            $io->section('Sélection des classes par jour');
            $targetDay = $this->parseDayOption($dayOpt, $io);

            // ⚠️ Ici on ne filtre PAS par spécialité, c’est vraiment « toutes les classes de ce jour ».
            $studyClasses = $this->studyClassRepository->findBy([
                'day' => $targetDay,
                'active'     => true,
                'classType'  => StudyClass::CLASS_TYPE_ARABE,
                'schoolYear' => StudyClass::SCHOOL_YEAR_2025_2026,
            ]);

            foreach ($studyClasses as $cls) {
                $io->text(sprintf('→ Classe #%d "%s" (jour=%s)', $cls->getId(), $cls->getName(), (string) $cls->getDay()));
            }
        }

        if (!$studyClasses) {
            $io->warning('Aucune classe trouvée avec les critères fournis (ID ou jour).');
            return Command::SUCCESS;
        }

        $motherSend  = 0;
        $sentCount  = 0;
        $skipped    = 0;
        $noLinkSkip = 0;

        // 3) Traitement des classes
        foreach ($studyClasses as $studyClass) {
            // Lien WhatsApp associé à la classe (peut être null)
            $whatsappUrl = $this->normalizeWhatsappUrl($studyClass->getWhatsappUrl());

            if (!$whatsappUrl) {
                $io->warning(sprintf(
                    'Classe "%s" (#%d) sans lien WhatsApp — aucune invitation envoyée pour cette classe.',
                    $studyClass->getName(),
                    $studyClass->getId()
                ));
                $noLinkSkip++;
                continue;
            }

            $io->section(sprintf(
                'Classe "%s" (#%d) – envoi des invitations (lien WhatsApp: %s)',
                $studyClass->getName(),
                $studyClass->getId(),
                $whatsappUrl
            ));

            // Élèves actifs pour cette classe
            $registrations = $this->classRegisteredRepository->findBy([
                'studyClass' => $studyClass,
                'active'     => true,
            ]);


            if (!$registrations) {
                $io->note('Aucun élève actif dans cette classe.');
                continue;
            }

            /** @var StudentClassRegistered $registration */
            forEach ($registrations as $registration) {
                $student = $registration->getStudent();
                $parent  = $student?->getParent();

                if (!$student || !$parent) {
                    $io->warning('Inscription sans élève ou sans parent — ignorée.');
                    $skipped++;
                    continue;
                }

                $emailTo = $parent->getEmailContact();
                if (!$emailTo) {
                    $io->warning(sprintf(
                        'Pas d’email parent pour %s — invitation non envoyée.',
                        $student->getFullName()
                    ));
                    $skipped++;
                    continue;
                }

                // Contexte transmis au template Twig
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
                    'whatsappUrl'     => $whatsappUrl,
                ];

                $subject = sprintf(
                    'Invitation au groupe WhatsApp – %s (%s, %s)',
                    $student->getFullName(),
                    $studyClass->getName(),
                    $yearLabel
                );

                if ($dryRun) {
                    $io->text(sprintf('[DRY RUN] À: %s | Sujet: %s', $emailTo, $subject));
                    $io->text(sprintf('          Lien WhatsApp: %s', $whatsappUrl));
                } else {
                    $sentCount++;
                    $this->mailService->sendEmail(
                        to: $emailTo,
                        subject: $subject,
                        template: 'email/send_invitation_whatsapp.html.twig',
                        context: $context,
                        sender: 'contact@ccib38.fr'
                    );
                    $emailToMother = $parent->getMotherEmail();
                    if ($emailToMother != $emailTo){
                        $motherSend ++;
                        $this->mailService->sendEmail(
                        to: $emailToMother,
                            subject: $subject,
                            template: 'email/send_invitation_whatsapp.html.twig',
                            context: $context,
                            sender: 'contact@ccib38.fr'
                        );
                    }
                }

            }

            $teacher = $studyClass->getPrincipalTeacher();
            if ($teacher === null) {
                $io->note('Aucun enseignant principal pour cette classe, pas d’email de notification envoyé.');
                continue;
            }
            $subject = sprintf(
                'Invitation au groupe WhatsApp – %s (%s, %s)',
                $teacher->getFirstName().' '.$teacher->getLastName(),
                $studyClass->getName(),
                $yearLabel
            );
            $context = [
                'yearLabel'       => $yearLabel,
                'teacherFullName'=> $teacher->getFirstName().' '.$teacher->getLastName(),
                'className'       => $studyClass->getName(),
                'classLevel'      => $studyClass->getLevelClass(),
                'speciality'      => $studyClass->getSpeciality(),
                'day'             => $studyClass->getDay(),
                'startHour'       => $studyClass->getStartHour(),
                'endHour'         => $studyClass->getEndHour(),
                'whatsappUrl'     => $whatsappUrl,
            ];
            $this->mailService->sendEmail(
                to: $teacher->getEmail(),
                subject: $subject,
                template: 'email/send_invitation_teacher_whatsapp.html.twig',
                context: $context,
                sender: 'contact@ccib38.fr'
            );

        }

        $io->success(sprintf(
            'Terminé. Invitations envoyées: %d | Mères envoyé : %d | Inscriptions ignorées (pb élève/parent/email): %d | Classes sans lien WhatsApp: %d%s',
            $sentCount,
            $motherSend,
            $skipped,
            $noLinkSkip,
            $dryRun ? ' (dry-run, aucun e-mail réellement envoyé)' : ''
        ));

        return Command::SUCCESS;
    }

    /**
     * Normalise l’URL du groupe WhatsApp :
     *  - null/chaine vide → null
     *  - sans http/https → préfixe "https://"
     */
    private function normalizeWhatsappUrl(?string $url): ?string
    {
        if (!$url) {
            return null;
        }

        $url = trim($url);
        if ($url == '') {
            return null;
        }

        if (preg_match('#^https?://#i', $url)) {
            return $url;
        }

        return 'https://' . $url;
    }

    /**
     * Convertit l’option --day en valeur attendue par StudyClass::getDay()/DB.
     * Accepte:
     *  - Noms FR/EN: lundi..dimanche / monday..sunday
     *  - Constantes: DAY_WEDNESDAY
     *  - Numérique: 1..7
     *
     * @return int|string
     */
    private function parseDayOption(string $dayOpt, SymfonyStyle $io): int|string
    {
        $raw = trim($dayOpt);

        // 1) Constante DAY_*
        if (str_starts_with(strtoupper($raw), 'DAY_')) {
            $const = StudyClass::class . '::' . strtoupper($raw);
            if (\defined($const)) {
                /** @var int|string $val */
                $val = \constant($const);
                return $val;
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

        // 3) Noms FR/EN
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
            $const = StudyClass::class . '::' . $map[$upper];
            if (\defined($const)) {
                /** @var int|string $val */
                $val = \constant($const);
                return $val;
            }
        }

        // Fallback: mercredi
        $io->note('Jour invalide, fallback sur mercredi.');
        /** @var int|string $wed */
        $wed = \defined(StudyClass::class . '::DAY_WEDNESDAY')
            ? \constant(StudyClass::class . '::DAY_WEDNESDAY')
            : 3;

        return $wed;
    }
}
