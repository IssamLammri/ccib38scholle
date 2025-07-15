<?php
namespace App\Service;

use App\Entity\RegistrationArabicCours;
use Doctrine\ORM\EntityManagerInterface;
use App\Service\MailService;

class RegistrationService
{
    private EntityManagerInterface $em;
    private MailService $mailService;

    /** Séquence des étapes dans l’ordre */
    private array $sequence = [
        RegistrationArabicCours::STEP_REGISTRATION,
        RegistrationArabicCours::STEP_LIST_WAITING,
        RegistrationArabicCours::STEP_PAYMENT,
        RegistrationArabicCours::STEP_VALIDATION,
        RegistrationArabicCours::STEP_DISTRIBUTION,
        RegistrationArabicCours::STEP_COMPTE_CREATION,
        RegistrationArabicCours::STEP_COMPLETED,
    ];

    public function __construct(
        EntityManagerInterface $em,
        MailService $mailService
    ) {
        $this->em          = $em;
        $this->mailService = $mailService;
    }

    /**
     * Retourne l'étape suivante ou null si on est à la dernière.
     */
    public function getNextStep(RegistrationArabicCours $reg): ?string
    {
        $current = $reg->getStepRegistration();
        $idx     = array_search($current, $this->sequence, true);

        if ($idx === false || $idx === count($this->sequence) - 1) {
            return null;
        }

        return $this->sequence[$idx + 1];
    }

    /**
     * Avance l'inscription d'une étape, envoie les mails si besoin, persiste et renvoie la nouvelle étape.
     *
     * @throws \DomainException si on ne peut pas avancer
     */
    public function advanceStep(RegistrationArabicCours $reg): string
    {
        $current = $reg->getStepRegistration();
        $next    = $this->getNextStep($reg);

        if ($next === null) {
            throw new \DomainException('Impossible d’avancer l’étape');
        }

        // Cas spéciaux avec envoi de mail
        if ($current === RegistrationArabicCours::STEP_LIST_WAITING
            && $next === RegistrationArabicCours::STEP_PAYMENT) {
            $this->mailService->sendWaitingToPayment($reg);
        }

        if ($current === RegistrationArabicCours::STEP_PAYMENT
            && $next === RegistrationArabicCours::STEP_VALIDATION) {
            $this->mailService->sendPaymentToValidation($reg);
        }

        // Mise à jour et flush
        $reg->setStepRegistration($next);
        $this->em->flush();

        return $next;
    }
}
