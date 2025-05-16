<?php

namespace App\Security;

use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;

class UserChecker implements UserCheckerInterface
{
    public function checkPreAuth(UserInterface $user): void
    {
        if (method_exists($user, 'getIsBanned') && $user->getIsBanned()) {
            throw new CustomUserMessageAccountStatusException('Votre compte a été banni.');
        }
    }

    public function checkPostAuth(UserInterface $user): void
    {
        // Rien ici pour le moment
    }
}
