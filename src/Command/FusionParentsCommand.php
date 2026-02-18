<?php
// src/Command/FusionParentsCommand.php

namespace App\Command;

use App\Entity\ParentEntity;
use App\Entity\Student;
use App\Entity\Invoice;
use App\Entity\Payment;
use App\Entity\Refund;
use App\Repository\ParentsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:fusion-parents',
    description: 'Fusionne 2 parents : remplace partout l’ancien parent par le parent conservé (students, invoices, payments, refunds).'
)]
class FusionParentsCommand extends Command
{
    public function __construct(
        private readonly ParentsRepository $parentRepo,
        private readonly EntityManagerInterface $em,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            // si tu veux passer directement en ligne de commande:
            ->addOption('id1', null, InputOption::VALUE_REQUIRED, 'ID parent 1')
            ->addOption('id2', null, InputOption::VALUE_REQUIRED, 'ID parent 2')
            ->addOption('dry-run', null, InputOption::VALUE_NONE, 'Simule sans écrire en base')
            ->addOption('delete-old', null, InputOption::VALUE_NONE, 'Supprimer le parent non conservé à la fin')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $helper = $this->getHelper('question');

        $dryRun = (bool) $input->getOption('dry-run');
        $deleteOld = (bool) $input->getOption('delete-old');

        // 1) Récupérer les 2 IDs (options OU questions interactives)
        $id1 = $input->getOption('id1');
        $id2 = $input->getOption('id2');

        if (!$id1) {
            $id1 = $io->ask('ID du parent #1 (ex: 12)', null, fn($v) => $this->validateId($v));
        }
        if (!$id2) {
            $id2 = $io->ask('ID du parent #2 (ex: 19)', null, fn($v) => $this->validateId($v));
        }

        $id1 = (int) $id1;
        $id2 = (int) $id2;

        if ($id1 === $id2) {
            $io->error('Les deux IDs sont identiques.');
            return Command::INVALID;
        }

        /** @var ParentEntity|null $p1 */
        $p1 = $this->parentRepo->find($id1);
        /** @var ParentEntity|null $p2 */
        $p2 = $this->parentRepo->find($id2);

        if (!$p1 || !$p2) {
            $io->error('Parent introuvable : vérifie les IDs.');
            return Command::FAILURE;
        }

        // 2) Choisir lequel garder
        $label1 = sprintf('%d - %s', $p1->getId(), method_exists($p1, '__toString') ? (string)$p1 : 'Parent #'.$p1->getId());
        $label2 = sprintf('%d - %s', $p2->getId(), method_exists($p2, '__toString') ? (string)$p2 : 'Parent #'.$p2->getId());

        $question = new ChoiceQuestion(
            'Quel parent veux-tu GARDER ? (l’autre sera remplacé partout)',
            [$label1, $label2],
            0
        );
        $keepLabel = $helper->ask($input, $output, $question);

        $keep = str_starts_with($keepLabel, (string)$p1->getId()) ? $p1 : $p2;
        $old  = ($keep === $p1) ? $p2 : $p1;

        $io->section('Résumé');
        $io->text('Garder : <info>'.$keep->getId().'</info>');
        $io->text('Remplacer : <comment>'.$old->getId().'</comment>  →  <info>'.$keep->getId().'</info>');
        $io->text('Mode : '.($dryRun ? '<comment>DRY-RUN (aucune écriture)</comment>' : '<info>WRITE</info>'));
        $io->newLine();

        // confirmation
        $confirm = new ConfirmationQuestion('Confirmer la fusion ? (y/N) ', false);
        if (!$helper->ask($input, $output, $confirm)) {
            $io->warning('Annulé.');
            return Command::SUCCESS;
        }

        // 3) Compter ce qui va bouger (pour afficher avant)
        $counts = $this->countAffected($old);

        $io->section('Éléments à mettre à jour');
        $io->listing([
            "Students : {$counts['students']}",
            "Invoices : {$counts['invoices']}",
            "Payments : {$counts['payments']}",
            "Refunds  : {$counts['refunds']}",
        ]);

        // 4) Transaction
        $this->em->wrapInTransaction(function () use ($io, $dryRun, $deleteOld, $keep, $old, $counts) {

            // STUDENTS
            $updatedStudents = $this->bulkUpdateParent(Student::class, $old, $keep);
            // INVOICES
            $updatedInvoices = $this->bulkUpdateParent(Invoice::class, $old, $keep);
            // PAYMENTS
            $updatedPayments = $this->bulkUpdateParent(Payment::class, $old, $keep);
            // REFUNDS
            $updatedRefunds  = $this->bulkUpdateParent(Refund::class, $old, $keep);

            $io->section('Résultat (UPDATE)');
            $io->listing([
                "Students modifiés : $updatedStudents",
                "Invoices modifiées : $updatedInvoices",
                "Payments modifiés : $updatedPayments",
                "Refunds modifiés  : $updatedRefunds",
            ]);

            if ($dryRun) {
                $io->warning('DRY-RUN activé : rollback automatique, aucune donnée écrite.');
                // en wrapInTransaction, on peut forcer une exception pour rollback
                throw new \RuntimeException('__DRY_RUN_ROLLBACK__');
            }

            // Optionnel : supprimer le vieux parent
            if ($deleteOld) {
                $io->text("Suppression du parent {$old->getId()} ...");
                $this->em->remove($old);
                $this->em->flush();
                $io->success("Parent {$old->getId()} supprimé.");
            }
        });

        // Si on arrive ici sans exception => OK
        $io->success('Fusion terminée ✅');
        return Command::SUCCESS;
    }

    private function validateId($v): int
    {
        $v = trim((string)$v);
        if ($v === '' || !ctype_digit($v) || (int)$v <= 0) {
            throw new \InvalidArgumentException('ID invalide.');
        }
        return (int)$v;
    }

    private function countAffected(ParentEntity $old): array
    {
        return [
            'students' => (int) $this->em->getRepository(Student::class)->count(['parent' => $old]),
            'invoices' => (int) $this->em->getRepository(Invoice::class)->count(['parent' => $old]),
            'payments' => (int) $this->em->getRepository(Payment::class)->count(['parent' => $old]),
            'refunds'  => (int) $this->em->getRepository(Refund::class)->count(['parent' => $old]),
        ];
    }

    /**
     * Update en masse via DQL (rapide)
     * @return int nombre de lignes modifiées
     */
    private function bulkUpdateParent(string $entityClass, ParentEntity $old, ParentEntity $keep): int
    {
        return $this->em->createQueryBuilder()
            ->update($entityClass, 'e')
            ->set('e.parent', ':keep')
            ->where('e.parent = :old')
            ->setParameter('keep', $keep)
            ->setParameter('old', $old)
            ->getQuery()
            ->execute();
    }
}
