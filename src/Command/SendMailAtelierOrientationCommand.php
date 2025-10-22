<?php

namespace App\Command;

use App\Entity\ParentEntity;
use App\Repository\ParentsRepository;
use App\Service\MailService;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class SendMailAtelierOrientationCommand extends Command
{
    // optionnel : définir le nom par défaut ici
    protected static $defaultName = 'app:send-mail-atelier-orientation';

    private MailService      $mailService;
    private LoggerInterface  $logger;
    public function __construct(
        MailService     $mailService,
        LoggerInterface $logger,
        ?string         $name = null              // ← ajouter ce 3ᵉ paramètre
    ) {
        parent::__construct($name);               // ← appeler le constructeur parent
        $this->mailService = $mailService;
        $this->logger      = $logger;
    }

    protected function configure(): void
    {
        $this
            ->setName('app:send-email-atelier-orientation')
            ->setDescription('Send email to all contacts');
    }


    /**
     * @throws \DateMalformedStringException
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $contacts = [
            ['id'=> 1,'fullName'=> 'Issam LAMMRI', 'email'=> 'cours.arabe.ccib38@gmail.com'],
            //['id'=> 1,'fullName'=> 'Issam LAMMRI', 'email'=> 'issamlammri5@gmail.com'],
           // ['id'=> 1,'fullName'=> 'Issam LAMMRI', 'email'=> 'soutien.scolaire.ccib38@gmail.com'],
           //['id'=> 0,'fullName'=> 'Khaoula CHEBIR', 'email'=> 'chebirkhaoula@yahoo.com'],
            //['id'=> 3,'fullName'=> 'Souleymen sahnoun', 'email'=> 'souleymen.sahnoun@gmail.com'],
        ];

        $parents = [];
        //$parents = $this->parentsRepository->findAll();

        foreach ($parents as $parent){
            /**
             * @var ParentEntity $parent
             */
            $contacts[] = [
                'id' => $parent->getId(),
                'fullName' => $parent->getFullNameParent(),
                'email' => $parent->getEmailContact(),
            ];
        }
        foreach ($contacts as $contact) {
            dump($contact['email']);
            $email = $contact['email'];
            $this->mailService->sendEmail(
                to: $email,
                subject: "Planning annuel 2025–2026 – Vacances, examens et fête de fin d’année",
                template: 'email/company/atelier-orientation.html.twig',
                context: [
                    'fullName' => $contact['fullName'],
                ],
                sender: "contact@ccib38.fr"
            );
        }


        $io->success('All sessions have been created successfully!');

        return Command::SUCCESS;
    }
}
