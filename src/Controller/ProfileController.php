<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ProfileController extends AbstractController
{
    #[Route('/profile', name: 'app_profile')]
    #[IsGranted("ROLE_USER")]
    public function index(): Response
    {
        $user= $this->getUser();
        $role = $user->getRoles()[0];

        $roleLabels = [
            'ROLE_USER' => 'Utilisateur',
            'ROLE_MODERATOR' => 'Modérateur',
            'ROLE_ADMIN' => 'Administrateur',
        ];

        return $this->render('profile/index.html.twig', [
            'user' => $user,
            'role' => $roleLabels[$role] ?? $role,
        ]);
    }
}
