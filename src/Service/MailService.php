<?php

namespace App\Service;

use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Twig\Environment;

class MailService
{
    private MailerInterface $mailer;
    private Environment $twig;

    public function __construct(MailerInterface $mailer, Environment $twig)
    {
        $this->mailer = $mailer;
        $this->twig = $twig;
    }

    public function sendEmail(string $to, string $subject, string $template, array $context = []): void
    {
        // Rendu du contenu HTML à partir du template
        $htmlContent = $this->twig->render($template, $context);

        // Création de l'e-mail avec un sender personnalisé
        $email = (new Email())
            ->from(new Address('ecole@ccib38.com', 'CCIB 38'))
            ->to($to)
            ->subject($subject)
            ->html($htmlContent);

        // Envoi de l'e-mail
        $this->mailer->send($email);
    }
}
