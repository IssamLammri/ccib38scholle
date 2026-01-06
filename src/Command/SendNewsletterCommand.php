<?php
// src/Command/SendNewsletterCommand.php

namespace App\Command;

use App\Entity\Contact;
use App\Repository\ContactRepository;
use App\Service\MailService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Exemples d'utilisation :
 *
 * 1) Envoi simple (sujet + template) à tous :
 *    php bin/console app:send-newsletter \
 *      --title="Newsletter Janvier" \
 *      --subject="🎉 Nouveautés de janvier" \
 *      --template="email/company/janvier_2026.html.twig" \
 *      --site-base-url="https://ccib38.fr"
 *
 * 2) Envoi avec variables Twig via un fichier JSON (context-file) :
 *    php bin/console app:send-newsletter \
 *      --subject="Inscriptions ouvertes ✅" \
 *      --template="email/company/academic_support_open_modern.html.twig" \
 *      --context-file="/var/www/newsletter.json" \
 *      --site-base-url="https://ccib38.fr"
 *
 * 3) Test sur une seule adresse (ignore filtres & limite) :
 *    php bin/console app:send-newsletter \
 *      --subject="Test newsletter" \
 *      --template="email/test.html.twig" \
 *      --test-email="moi@domaine.fr"
 *
 * 4) Dry-run (n'envoie rien, affiche seulement ce qui serait envoyé) :
 *    php bin/console app:send-newsletter \
 *      --subject="Prévisualisation" \
 *      --template="email/test.html.twig" \
 *      --dry-run
 *
 * 5) Cibler des segments/tags (répétable ou CSV) :
 *    php bin/console app:send-newsletter \
 *      --subject="Offre spéciale" \
 *      --template="email/company/promo.html.twig" \
 *      --segments="parents,lyceens" \
 *      --tags="vip" \
 *      --site-base-url="https://ccib38.fr"
 *
 * 6) Pagination (envoyer par morceaux) + limite :
 *    php bin/console app:send-newsletter \
 *      --subject="Envoi par batch" \
 *      --template="email/company/batch.html.twig" \
 *      --offset=0 --limit=500 \
 *      --batch-size=100 \
 *      --site-base-url="https://ccib38.fr"
 *
 * 7) Changer l'expéditeur :
 *    php bin/console app:send-newsletter \
 *      --subject="Newsletter" \
 *      --template="email/company/default.html.twig" \
 *      --sender="contact@ccib38.fr"
 */
#[AsCommand(
    name: 'app:send-newsletter',
    description: 'Envoie une newsletter générique (sujet + template paramétrables).'
)]
class SendNewsletterCommand extends Command
{
    public function __construct(
        private readonly ContactRepository $contactRepository,
        private readonly EntityManagerInterface $em,
        private readonly MailService $mailService,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('title', null, InputOption::VALUE_REQUIRED, 'Titre affiché dans la console', 'Envoi newsletter')
            ->addOption('site-base-url', null, InputOption::VALUE_REQUIRED, 'Base URL (ex: https://ccib38.fr) pour générer le lien de désabonnement')

            ->addOption('subject', null, InputOption::VALUE_REQUIRED, 'Sujet de l’email')
            ->addOption('template', null, InputOption::VALUE_REQUIRED, 'Chemin du template Twig (ex: email/company/newsletter.html.twig)')
            ->addOption('sender', null, InputOption::VALUE_REQUIRED, 'Email expéditeur', 'contact@ccib38.fr')

            ->addOption('context-file', null, InputOption::VALUE_REQUIRED, 'Fichier JSON pour enrichir le contexte Twig (optionnel)')

            ->addOption('segments', null, InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, 'Segments à cibler (répéter l’option ou CSV)')
            ->addOption('tags', null, InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, 'Tags à cibler (répéter l’option ou CSV)')
            ->addOption('limit', null, InputOption::VALUE_REQUIRED, 'Limiter le nombre d’envois (0 = illimité)', 0)

            ->addOption('test-email', null, InputOption::VALUE_REQUIRED, 'N’envoie qu’à cet email (ignore filtres & limite)')
            ->addOption('offset', null, InputOption::VALUE_REQUIRED, 'Décalage de départ (pour paginer les envois)', 0)
            ->addOption('dry-run', null, InputOption::VALUE_NONE, 'N’envoie rien, affiche seulement ce qui serait envoyé')

            ->addOption('batch-size', null, InputOption::VALUE_REQUIRED, 'Taille de lot pour flush (optimisation)', 100);
    }

    protected function execute(InputInterface $input, \Symfony\Component\Console\Output\OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $title       = (string) $input->getOption('title');
        $siteBaseUrl = (string) ($input->getOption('site-base-url') ?? '');
        $subject     = (string) ($input->getOption('subject') ?? '');
        $template    = (string) ($input->getOption('template') ?? '');
        $sender      = (string) ($input->getOption('sender') ?? 'contact@ccib38.fr');

        $dryRun    = (bool) $input->getOption('dry-run');
        $limit     = max(0, (int) $input->getOption('limit'));
        $batchSize = max(1, (int) $input->getOption('batch-size'));
        $testEmail = $input->getOption('test-email');
        $offset    = max(0, (int) $input->getOption('offset'));

        $io->title($title);

        if ($subject === '') {
            $io->error('Option --subject obligatoire.');
            return Command::FAILURE;
        }
        if ($template === '') {
            $io->error('Option --template obligatoire.');
            return Command::FAILURE;
        }

        $extraContext = $this->loadJsonContext((string) $input->getOption('context-file'), $io);

        // Parse arrays (segments/tags)
        $segmentsFilter = $this->parseArrayOption($input->getOption('segments'));
        $tagsFilter     = $this->parseArrayOption($input->getOption('tags'));

        // Récupération des destinataires
        $recipients = [];
        if (is_string($testEmail) && $testEmail !== '') {
            $contact = $this->contactRepository->findOneByEmail($testEmail);
            if (!$contact) {
                $io->warning(sprintf('Contact "%s" introuvable.', $testEmail));
            } else {
                $recipients = [$contact];
                $io->note(sprintf('Mode test — seul %s recevra le mail.', $testEmail));
            }
        } else {
            $qb = $this->contactRepository->createQueryBuilder('c')
                ->andWhere('c.email IS NOT NULL AND c.email <> \'\'')
                ->orderBy('c.id', 'ASC');

            if ($offset > 0) {
                $qb->setFirstResult($offset);
            }
            if ($limit > 0) {
                $qb->setMaxResults($limit);
            }

            /** @var Contact[] $base */
            $base = $qb->getQuery()->getResult();

            $recipients = array_values(array_filter($base, function (Contact $c) use ($segmentsFilter, $tagsFilter): bool {
                if ($segmentsFilter) {
                    $seg = array_map('strval', $c->getSegments());
                    if (count(array_intersect($segmentsFilter, $seg)) === 0) return false;
                }
                if ($tagsFilter) {
                    $tags = array_map('strval', $c->getTags());
                    if (count(array_intersect($tagsFilter, $tags)) === 0) return false;
                }
                return true;
            }));
        }

        if (!$recipients) {
            $io->warning('Aucun destinataire trouvé avec les critères donnés.');
            return Command::SUCCESS;
        }

        $io->section(sprintf('Destinataires: %d', count($recipients)));

        // Envoi
        $sent = 0;
        $skipped = 0;
        $i = 0;

        foreach ($recipients as $contact) {
            $to = trim((string) $contact->getEmail());
            if ($to === '') {
                $skipped++;
                continue;
            }

            $unsubscribeUrl = '';
            if ($siteBaseUrl) {
                $unsubscribeUrl = rtrim($siteBaseUrl, '/') . '/newsletter/unsubscribe/' . $contact->getPublicToken();
            }

            $context = array_merge([
                'contact'        => $contact,
                'fullName'       => $contact->getFullName() ?: $to,
                'unsubscribeUrl' => $unsubscribeUrl,
            ], $extraContext);

            if ($dryRun) {
                $io->text(sprintf('[DRY RUN] À: %s | Sujet: %s | Template: %s', $to, $subject, $template));
            } else {
                $this->mailService->sendEmail(
                    to: $to,
                    //to: 'issamlammri5@gmail.com',
                    subject: $subject,
                    template: $template,
                    context: $context,
                    sender: $sender
                );

                $contact->setLastEmailedAt(new \DateTimeImmutable());
                $sent++;
            }

            if (!$dryRun && (++$i % $batchSize) === 0) {
                $this->em->flush();
            }
        }

        if (!$dryRun) {
            $this->em->flush();
        }

        $io->note(sprintf('Fenêtre d’envoi: offset=%d, limit=%d (ordre: id ASC)', $offset, $limit));
        $io->success(sprintf(
            'Terminé. Emails envoyés: %d | Ignorés: %d%s',
            $sent, $skipped, $dryRun ? ' (dry-run)' : ''
        ));

        return Command::SUCCESS;
    }

    private function loadJsonContext(string $contextFile, SymfonyStyle $io): array
    {
        if ($contextFile === '') {
            return [];
        }
        if (!is_file($contextFile)) {
            $io->warning(sprintf('context-file introuvable: %s (ignoré)', $contextFile));
            return [];
        }

        $raw = file_get_contents($contextFile);
        if ($raw === false) {
            $io->warning(sprintf('Impossible de lire context-file: %s (ignoré)', $contextFile));
            return [];
        }

        $data = json_decode($raw, true);
        if (!is_array($data)) {
            $io->warning(sprintf('context-file JSON invalide: %s (ignoré)', $contextFile));
            return [];
        }

        return $data;
    }

    /**
     * @param mixed $rawOption (array<string>|string|null)
     * @return string[]
     */
    private function parseArrayOption(mixed $rawOption): array
    {
        $values = [];
        foreach ((array)($rawOption ?? []) as $chunk) {
            foreach (preg_split('/\s*,\s*/', (string)$chunk, -1, PREG_SPLIT_NO_EMPTY) ?: [] as $val) {
                $values[] = (string)$val;
            }
        }
        return array_values(array_unique($values));
    }
}
