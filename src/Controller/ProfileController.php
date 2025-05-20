<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Entity\User;
use App\Form\EditProfileType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

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
    #[Route('/profile/edit', name: 'app_profile_edit')]
    public function edit(Request $request, UserPasswordHasherInterface $passwordHasher, EntityManagerInterface $em): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $form = $this->createForm(EditProfileType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Récupération du mot de passe s’il a été modifié
            $plainPassword = $form->get('plainPassword')->getData();

            if (!empty($plainPassword)) {
                $hashedPassword = $passwordHasher->hashPassword($user, $plainPassword);
                $user->setPassword($hashedPassword);
            }

            $em->flush();
            $this->addFlash('success', 'Profil mis à jour.');
            return $this->redirectToRoute('app_profile');
        }

        return $this->render('profile/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }

}

