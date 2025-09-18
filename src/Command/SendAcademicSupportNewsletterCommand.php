<?php
// src/Command/SendAcademicSupportNewsletterCommand.php

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

#[AsCommand(
    name: 'app:send-academic-support-newsletter',
    description: 'Envoie la newsletter annonçant l’ouverture des inscriptions au soutien scolaire.'
)]
class SendAcademicSupportNewsletterCommand extends Command
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
            // Lien base pour désabonnement (facultatif)
            ->addOption('site-base-url', null, InputOption::VALUE_REQUIRED, 'Base URL (ex: https://ccib38.fr) pour générer le lien de désabonnement')
            // Sujet personnalisable
            ->addOption('subject', null, InputOption::VALUE_REQUIRED, 'Sujet de l’email', 'Soutien scolaire : les inscriptions sont ouvertes !')
            // Filtrage
            ->addOption('segments', null, InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, 'Segments à cibler (répéter l’option ou CSV)')
            ->addOption('tags', null, InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, 'Tags à cibler (répéter l’option ou CSV)')
            ->addOption('limit', null, InputOption::VALUE_REQUIRED, 'Limiter le nombre d’envois (0 = illimité)', 0)
            // Test & dry-run
            ->addOption('test-email', null, InputOption::VALUE_REQUIRED, 'N’envoie qu’à cet email (ignore filtres & limite)')
            ->addOption('offset', null, InputOption::VALUE_REQUIRED, 'Décalage de départ (pour paginer les envois)', 0)
            ->addOption('dry-run', null, InputOption::VALUE_NONE, 'N’envoie rien, affiche seulement ce qui serait envoyé')
            // Batch
            ->addOption('batch-size', null, InputOption::VALUE_REQUIRED, 'Taille de lot pour flush (optimisation)', 100);
    }

    protected function execute(InputInterface $input, \Symfony\Component\Console\Output\OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Inscriptions au soutien scolaire');

        $siteBaseUrl = (string) ($input->getOption('site-base-url') ?? '');
        $subject     = (string) $input->getOption('subject');
        $dryRun      = (bool)   $input->getOption('dry-run');
        $limit       = max(0, (int) $input->getOption('limit'));
        $batchSize   = max(1, (int) $input->getOption('batch-size'));
        $testEmail   = $input->getOption('test-email');
        $offset = max(0, (int) $input->getOption('offset'));


        // Parse arrays (segments/tags) depuis options multiples ou CSV
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
                ->orderBy('c.id', 'ASC')
                ->setFirstResult($offset)
                ->setMaxResults($limit);


            if ($offset > 0) {
                $qb->setFirstResult($offset);
            }
            if ($limit > 0) {
                $qb->setMaxResults($limit);
            }


            /** @var Contact[] $base */
            $base = $qb->getQuery()->getResult();

            // Filtrage segments/tags côté PHP (simple/portable)
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
        if ($dryRun) {
            foreach ($recipients as $c) {
                $io->text(sprintf('[DRY RUN] %s <%s>', $c->getFullName() ?: '—', $c->getEmail()));
            }
        }

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

            // Lien de désabonnement (si base fournie)
            $unsubscribeUrl = '';
            if ($siteBaseUrl) {
                $unsubscribeUrl = rtrim($siteBaseUrl, '/') . '/newsletter/unsubscribe/' . $contact->getPublicToken();
            }

            // Le template gère l’URL d’inscription par défaut, on ne passe rien ici.
            $context = [
                'contact'        => $contact,
                'fullName'       => $contact->getFullName() ?: $to,
                'unsubscribeUrl' => $unsubscribeUrl,
            ];

            if ($dryRun) {
                $io->text(sprintf('[DRY RUN] À: %s | Sujet: %s', $to, $subject));
            } else {
                $this->mailService->sendEmail(
                    to: $to,
                    subject: $subject,
                    // utilise le template “bulletproof” proposé
                    template: 'email/company/academic_support_open_modern.html.twig',
                    context: $context,
                    sender: 'contact@ccib38.fr'
                );

                $contact->setLastEmailedAt(new \DateTimeImmutable());
                $sent++;
            }

            // Batch flush
            if (!$dryRun && (++$i % $batchSize) === 0) {
                $this->em->flush();
              //  $this->em->clear();
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
