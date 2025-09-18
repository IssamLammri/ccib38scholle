<?php
// src/Command/CreateContactDataCommand.php

namespace App\Command;

use App\Entity\Contact;
use App\Repository\ContactRepository;
use App\Repository\ParentsRepository;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:create-contact-data',
    description: 'Crée/Met à jour des contacts à partir des parents (père/mère).'
)]
class CreateContactDataCommand extends Command
{
    public function __construct(
        private readonly ParentsRepository $parentRepo,
        private readonly ContactRepository $contactRepo,
        private readonly EntityManagerInterface $em
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('dry-run', null, InputOption::VALUE_NONE, 'Simule sans écrire en base')
            ->addOption('limit', null, InputOption::VALUE_REQUIRED, 'Limiter le nombre de parents traités', null)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io      = new SymfonyStyle($input, $output);
        $dryRun  = (bool) $input->getOption('dry-run');
        $limit   = $input->getOption('limit');

        $io->title('Import/MAJ des contacts à partir des parents');
        if ($dryRun) {
            $io->warning('Mode DRY-RUN activé : aucune écriture ne sera effectuée.');
        }

        // Récupération des parents
        $parents = $limit
            ? $this->parentRepo->createQueryBuilder('p')->setMaxResults((int)$limit)->getQuery()->getResult()
            : $this->parentRepo->findAll();

        $created = 0;
        $updated = 0;
        $skipped = 0;

        $rows = [];
        $seenEmails = [];

        $io->progressStart(\count($parents));
        foreach ($parents as $parent) {
            // PÈRE
            $stats = $this->upsertContactFromParentPiece(
                email: $parent->getFatherEmail(),
                firstName: $parent->getFatherFirstName(),
                lastName: $parent->getFatherLastName(),
                phone: $parent->getFatherPhone(),
                // 👇 fallback depuis la MÈRE
                altFirstName: $parent->getMotherFirstName(),
                altLastName:  $parent->getMotherLastName(),
                familyStatus: $parent->getFamilyStatus(),
                seenEmails:   $seenEmails,
                dryRun:       $dryRun
            );

            // MÈRE
            $stats2 = $this->upsertContactFromParentPiece(
                email: $parent->getMotherEmail(),
                firstName: $parent->getMotherFirstName(),
                lastName: $parent->getMotherLastName(),
                phone: $parent->getMotherPhone(),
                // 👇 fallback depuis le PÈRE
                altFirstName: $parent->getFatherFirstName(),
                altLastName:  $parent->getFatherLastName(),
                familyStatus: $parent->getFamilyStatus(),
                seenEmails:   $seenEmails,
                dryRun:       $dryRun
            );

            $created += $stats['created'] + $stats2['created'];
            $updated += $stats['updated'] + $stats2['updated'];
            $skipped += $stats['skipped'] + $stats2['skipped'];

            if (($stats['line'] ?? null))  { $rows[] = $stats['line']; }
            if (($stats2['line'] ?? null)) { $rows[] = $stats2['line']; }

            $io->progressAdvance();
        }
        $io->progressFinish();

        if (!$dryRun) {
            try {
                $this->em->flush();
            } catch (UniqueConstraintViolationException $e) {
                $io->error('Contrainte d’unicité violée sur email (vérifier les doublons). Détails : '.$e->getMessage());
                return Command::FAILURE;
            }
        }

        // Tableau récap
        $io->section('Résumé des opérations');
        if ($rows === []) {
            $rows[] = ['-', '-', '-', '-', '-', '-', 'Aucune donnée traitée'];
        }
        $io->table(
            ['Email', 'Prénom', 'Nom', 'Téléphone', 'Tags', 'Status', 'Action'],
            $rows
        );

        $io->success(sprintf(
            'Terminé : %d créé(s), %d mis à jour, %d ignoré(s) — %d parent(s) parcouru(s).',
            $created, $updated, $skipped, \count($parents)
        ));

        return Command::SUCCESS;
    }

    /**
     * Crée/MàJ un Contact à partir d’un « morceau » de Parent (père ou mère).
     *
     * @return array{created:int,updated:int,skipped:int,line?:array}
     */
    private function upsertContactFromParentPiece(
        ?string $email,
        ?string $firstName,
        ?string $lastName,
        ?string $phone,
        ?string $altFirstName,   // 👈 ajouté
        ?string $altLastName,    // 👈 ajouté
        ?string $familyStatus,
        array   &$seenEmails,
        bool    $dryRun
    ): array {
        $email = $this->normalizeEmail($email);
        if (!$email || $this->isPlaceholderEmail($email) || \in_array($email, $seenEmails, true)) {
            return ['created' => 0, 'updated' => 0, 'skipped' => 1];
        }

        // Nettoyage primaire
        $first = $this->sanitizeName($firstName);
        $last  = $this->sanitizeName($lastName);
        $phone = $this->normalizePhone($phone);

        // 👇 Fallback champ par champ depuis l’autre parent
        if (!$first) $first = $this->sanitizeName($altFirstName);
        if (!$last)  $last  = $this->sanitizeName($altLastName);

        $contact = $this->contactRepo->findOneBy(['email' => $email]);

        $action = 'mis à jour';
        if (!$contact) {
            $contact = new Contact();
            $contact->setEmail($email);
            $contact->setLocale('fr');
            $contact->setStatus(Contact::STATUS_SUBSCRIBED);
            $contact->setSegments(['newsletter']);
            $action = 'créé';
            if (!$dryRun) $this->em->persist($contact);
        }

        // Non destructif : on remplit seulement si vide
        if (!$contact->getFirstName() && $first) $contact->setFirstName($first);
        if (!$contact->getLastName()  && $last)  $contact->setLastName($last);
        if (!$contact->getPhone()     && $phone) $contact->setPhone($phone);

        // Tags
        $tags = $contact->getTags();
        if (!\in_array('parent', $tags, true)) $tags[] = 'parent';
        if ($familyStatus) {
            $slug = match (mb_strtolower($familyStatus)) {
                'married', 'marié', 'mariés'       => 'parents-maries',
                'divorced', 'divorcé', 'divorcés'  => 'parents-divorces',
                default => null,
            };
            if ($slug && !\in_array($slug, $tags, true)) $tags[] = $slug;
        }
        $contact->setTags($tags);

        $seenEmails[] = $email;

        $line = [
            $email,
            $contact->getFirstName() ?? '',
            $contact->getLastName() ?? '',
            $contact->getPhone() ?? '',
            implode(',', $contact->getTags()),
            $contact->getStatus(),
            $dryRun ? "($action) DRY-RUN" : $action,
        ];

        return [
            'created' => $action === 'créé' ? 1 : 0,
            'updated' => $action === 'mis à jour' ? 1 : 0,
            'skipped' => 0,
            'line'    => $line,
        ];
    }



    private function normalizeEmail(?string $email): ?string
    {
        if (!$email) return null;
        $email = trim(mb_strtolower($email));
        // très simple validation ; la contrainte @Assert\Email fera le reste
        return filter_var($email, FILTER_VALIDATE_EMAIL) ? $email : null;
    }

    private function isPlaceholderEmail(string $email): bool
    {
        // Évite d’importer des emails factices de secours, ex : ecole@ccib38.com
        $placeholders = [
            'ecole@ccib38.com',
            'no-reply@exemple.com',
            'noreply@exemple.com',
            'vide@vide.com',
        ];
        return \in_array($email, $placeholders, true);
    }

    private function sanitizeName(?string $value): ?string
    {
        if (!$value) return null;
        $v = trim($value);
        if ($v === '' || mb_strtolower($v) === 'vide') return null;
        return $v;
    }

    private function normalizePhone(?string $phone): ?string
    {
        if (!$phone) return null;
        $digits = preg_replace('/\D+/', '', $phone);
        if ($digits === '') return null;
        // Optionnel : format FR simple en 0X XX XX XX XX
        if (strlen($digits) === 10 && str_starts_with($digits, '0')) {
            return vsprintf('%s%s %s%s %s%s %s%s %s%s', str_split($digits));
        }
        return $phone; // sinon laisse tel quel
    }
}
