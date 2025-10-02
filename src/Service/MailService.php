<?php

namespace App\Service;

use App\Entity\RegistrationArabicCours;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class MailService
{
    private MailerInterface $mailer;
    private Environment $twig;

    public function __construct(MailerInterface $mailer, Environment $twig)
    {
        $this->mailer = $mailer;
        $this->twig = $twig;
    }

    /**
     * Envoie un email avec ou sans pièce jointe.
     *
     * @param string $to L'adresse email du destinataire
     * @param string $subject Le sujet de l'email
     * @param string $template Le chemin du template Twig
     * @param array $context Les données à transmettre au template
     * @param string|null $attachmentPath Le chemin du fichier à attacher (optionnel)
     * @throws TransportExceptionInterface Si l'envoi de l'email échoue.
     * @throws LoaderError Si le template Twig est introuvable.
     * @throws RuntimeError Si une erreur d'exécution survient dans le template.
     * @throws SyntaxError Si le template contient une erreur de syntaxe.
     * @throws \Exception
     */
    public function sendEmail(string $to, string $subject, string $template, array $context = [], ?string $attachmentPath = null, ?string $sender = null,?string $senderName = null): void
    {
        try {
            // Rendu du contenu HTML à partir du template
            $htmlContent = $this->twig->render($template, $context);
        } catch (LoaderError $e) {
            throw new \Exception('Erreur de chargement du template Twig : ' . $e->getMessage(), 0, $e);
        } catch (RuntimeError $e) {
            throw new \Exception('Erreur d’exécution lors du rendu du template Twig : ' . $e->getMessage(), 0, $e);
        } catch (SyntaxError $e) {
            throw new \Exception('Erreur de syntaxe dans le template Twig : ' . $e->getMessage(), 0, $e);
        }
        $senderMail = $sender ?? 'contact@ccib38.fr';
        //$senderMail = $sender ?? 'ecole@ccib38.com';
        if ($senderName === null) {
            $senderName = 'CCIB38';
        }
        $email = (new Email())
            ->from(new Address($senderMail, $senderName))
            ->to($to)
            ->subject($subject)
            ->html($htmlContent);

        if ($attachmentPath) {
            if (file_exists($attachmentPath)) {
                $email->attachFromPath($attachmentPath);
            } else {
                throw new \InvalidArgumentException('Le fichier à attacher est introuvable : ' . $attachmentPath);
            }
        }

        try {
            $this->mailer->send($email);
        } catch (TransportExceptionInterface $e) {
            throw new \Exception('Erreur lors de l’envoi de l’email : ' . $e->getMessage(), 0, $e);
        }
    }

    public function sendWaitingToPayment(RegistrationArabicCours $reg): void
    {
        $subject = sprintf(
            'Candidature approuvée pour 2025/2026 [%s %s] & règlement des frais requis',
            $reg->getChildFirstName(),
            $reg->getChildLastName()
        );
        $this->sendEmail(
            to:       $reg->getContactEmail(),
            subject:  $subject,
            template: 'email/company/pass-to-payment-step.html.twig',
            context: [
                'fullNameStudent' => $reg->getChildFirstName() . ' ' . $reg->getChildLastName(),
                'token'           => $reg->getToken(),
            ],
            sender:   'contact@ccib38.fr'
        );
    }

    public function sendPaymentToValidation(RegistrationArabicCours $reg): void
    {
        $fullName = $reg->getChildFirstName() . ' ' . $reg->getChildLastName();
        $subject  = sprintf('Paiement initié – Validation en cours [%s]', $fullName);

        $this->sendEmail(
            to:       $reg->getContactEmail(),
            subject:  $subject,
            template: 'email/company/pass-to-validation-step-styled.html.twig',
            context: [
                'fullNameStudent' => $fullName,
                'token'           => $reg->getToken(),
            ],
            sender:   'contact@ccib38.fr'
        );
    }

}
