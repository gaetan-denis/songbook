<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class ProfileController extends AbstractController
{
    #[Route('/profile', name: 'app_profile')]
    #[IsGranted("ROLE_USER")]
    public function index(): Response
    {
        $user = $this->getUser();
        // On récupère le rôle principal de l'utilisateur
        $roles = $user->getRoles();

        // Vérification si l'utilisateur a des rôles
        if (empty($roles)) {
            throw $this->createAccessDeniedException('Utilisateur sans rôle valide');
        }

        // Le premier rôle (par défaut, ce sera ROLE_USER ou autre)
        $role = $roles[0];

        // On définit les labels pour chaque rôle
        $roleLabels = [
            'ROLE_USER' => 'Utilisateur',
            'ROLE_MODERATOR' => 'Modérateur',
            'ROLE_ADMIN' => 'Administrateur',
        ];

        // Si le rôle existe dans les labels, on l'affiche
        $roleLabel = $roleLabels[$role] ?? 'Rôle inconnu';

        return $this->render('profile/index.html.twig', [
            'role' => $roleLabel,  // Passer le rôle sous forme de label
            'user' => $user,
        ]);
    }
}

