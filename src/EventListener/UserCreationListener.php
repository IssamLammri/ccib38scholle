<?php
namespace App\EventListener;

use App\Entity\User;
use App\Service\UserService;
use Doctrine\Persistence\Event\LifecycleEventArgs;

class UserCreationListener
{
    public function __construct(private UserService $userService)
    {
    }

    public function postPersist(LifecycleEventArgs $args): void
    {
        $entity = $args->getObject();

        // Vérifiez si l'entité est de type User
        if (!$entity instanceof User) {
            return;
        }

        // Appeler le service pour envoyer l'email
        $this->userService->sendEmailCreationUserAndVerified($entity);
    }
}
