<?php

// AdminController.php
namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

// Utilise Annotation pour la route

final class AdminController extends AbstractController
{
    #[Route('/admin', name: 'app_admin')]
    public function index(): Response
    {
        return $this->render('admin/index.html.twig');
    }

    #[Route('/admin/users', name: 'app_admin_users')]
    public function manageUsers(UserRepository $userRepository): Response
    {
        $users = $userRepository->findAll();

        return $this->render('admin/users.html.twig', [
            'users' => $users,
        ]);
    }

    #[Route('/admin/partitions', name: 'app_admin_partitions')]
    public function managePartitions(): Response
    {
// Logique pour gérer les partitions
        return $this->render('admin/partitions.html.twig');
    }

    #[Route('/admin/settings', name: 'app_admin_settings')]
    public function settings(): Response
    {
// Logique pour gérer les paramètres
        return $this->render('admin/settings.html.twig');
    }
    #[Route('/admin/user/{id}/edit', name: 'user_edit')]
    public function edit(User $user, Request $request, EntityManagerInterface $em): Response
    {
        $currentUser = $this->getUser(); // Utilisateur connecté

        if (!$currentUser) {
            throw $this->createAccessDeniedException('Vous devez être connecté.');
        }

        // Empêcher les modérateurs de modifier d'autres modérateurs ou admins
        if (in_array('ROLE_MODERATOR', $currentUser->getRoles(), true)) {
            if (
                in_array('ROLE_ADMIN', $user->getRoles(), true) ||
                in_array('ROLE_MODERATOR', $user->getRoles(), true)
            ) {
                $this->addFlash('error', 'Vous ne pouvez pas modifier cet utilisateur.');
                return $this->redirectToRoute('app_admin_users');
            }
        }

        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();

            $this->addFlash('success', 'Utilisateur modifié avec succès.');
            return $this->redirectToRoute('app_admin_users');
        }

        return $this->render('admin/edit_user.html.twig', [
            'form' => $form->createView(),
            'user' => $user,
        ]);
    }
    #[Route('/admin/user/{id}/delete', name: 'user_delete', methods: ['POST'])]
    public function delete(User $user, Request $request, EntityManagerInterface $em): Response
    {
        $currentUser = $this->getUser();

        if (!$currentUser) {
            throw $this->createAccessDeniedException('Vous devez être connecté.');
        }

        // Vérification de sécurité
        if (in_array('ROLE_MODERATOR', $currentUser->getRoles(), true)) {
            if (
                in_array('ROLE_ADMIN', $user->getRoles(), true) ||
                in_array('ROLE_MODERATOR', $user->getRoles(), true)
            ) {
                $this->addFlash('error', 'Vous ne pouvez pas supprimer cet utilisateur.');
                return $this->redirectToRoute('app_admin_users');
            }
        }

        // Vérification du token CSRF
        if ($this->isCsrfTokenValid('delete-user-' . $user->getId(), $request->request->get('_token'))) {
            $em->remove($user);
            $em->flush();

            $this->addFlash('success', 'Utilisateur supprimé avec succès.');
        } else {
            $this->addFlash('error', 'Token CSRF invalide.');
        }

        return $this->redirectToRoute('app_admin_users');
    }
    #[Route('/admin/user/{id}/ban', name: 'user_ban', methods: ['POST'])]
    public function ban(User $user, EntityManagerInterface $em): Response
    {
        $currentUser = $this->getUser();

        if (!$currentUser) {
            throw $this->createAccessDeniedException('Vous devez être connecté.');
        }

        // Vérification de sécurité : les modérateurs ne peuvent pas bannir les admins ou les autres modérateurs
        if (in_array('ROLE_MODERATOR', $currentUser->getRoles(), true)) {
            if (
                in_array('ROLE_ADMIN', $user->getRoles(), true) ||
                in_array('ROLE_MODERATOR', $user->getRoles(), true)
            ) {
                $this->addFlash('error', 'Vous ne pouvez pas bannir cet utilisateur.');
                return $this->redirectToRoute('app_admin_users');
            }
        }

        // On inverse l'état de bannissement
        $user->setIsBanned(!$user->getIsBanned());
        $em->flush();

        // Flash message en fonction de l'état de bannissement
        $this->addFlash(
            'success',
            $user->getIsBanned() ? 'Utilisateur banni avec succès.' : 'Utilisateur rétabli avec succès.'
        );

        return $this->redirectToRoute('app_admin_users');
    }
}
