<?php

namespace App\Service;

use App\Entity\User;

class UserService
{

    public function __construct(private MailService $mailService)
    {

    }

    public function sendEmailCreationUserAndVerified(User $user): void
    {
        $this->mailService->sendEmail(
            $user->getEmail(),
            'Bienvenue à l\'École CCIB 38 : Activez votre compte dès maintenant',
            'email/creation_new_user.html.twig',
            ['user' => $user]
        );
        
    }
}
