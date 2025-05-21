<?php

// AdminController.php
namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\RoleRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

// Utilise Annotation pour la route

final class AdminController extends AbstractController
{
    #[Route('/admin', name: 'app_admin')]
    #[IsGranted('ROLE_MODERATOR')]
    public function index(): Response
    {
        return $this->render('admin/index.html.twig');
    }

    #[Route('/admin/users', name: 'app_admin_users')]
    #[IsGranted('ROLE_MODERATOR')]

    public function manageUsers(UserRepository $userRepository): Response
    {
        $users = $userRepository->findAll();

        return $this->render('admin/users.html.twig', [
            'users' => $users,
        ]);
    }

    #[Route('/admin/partitions', name: 'app_admin_partitions')]
    #[IsGranted('ROLE_MODERATOR')]
    public function managePartitions(): Response
    {
// Logique pour gérer les partitions
        return $this->render('admin/partitions.html.twig');
    }

    #[Route('/admin/settings', name: 'app_admin_settings')]
    #[IsGranted('ROLE_ADMIN')]
    public function settings(): Response
    {
// Logique pour gérer les paramètres
        return $this->render('admin/settings.html.twig');
    }

    #[Route('/admin/user/{id}/delete', name: 'user_delete', methods: ['POST'])]
    #[IsGranted('ROLE_MODERATOR')]
    public function delete(User $user, Request $request, EntityManagerInterface $em): Response
    {

        $currentUser = $this->getUser();

        // Un utilisateur ne peut pas se bannir lui-même (admin ou modérateur)
        if ($user === $currentUser) {
            $this->addFlash('error', 'Vous ne pouvez pas vous bannir vous-même.');
            return $this->redirectToRoute('app_admin_users');
        }

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
    #[IsGranted('ROLE_MODERATOR')]
    public function ban(User $user, Request $request, EntityManagerInterface $em): Response
    {
        $currentUser = $this->getUser();

        if (!$currentUser) {
            throw $this->createAccessDeniedException('Vous devez être connecté.');
        }

        // Empêche un utilisateur de se bannir lui-même
        if ($user === $currentUser) {
            $this->addFlash('error', 'Vous ne pouvez pas vous bannir vous-même.');
            return $this->redirectToRoute('app_admin_users');
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

        // Vérification du token CSRF
        if (!$this->isCsrfTokenValid('ban-user-' . $user->getId(), $request->request->get('_token'))) {
            $this->addFlash('error', 'Token CSRF invalide.');
            return $this->redirectToRoute('app_admin_users');
        }

        // On inverse l'état de bannissement
        $user->setIsBanned(!$user->getIsBanned());
        $em->flush();

        $this->addFlash(
            'success',
            $user->getIsBanned() ? 'Utilisateur banni avec succès.' : 'Utilisateur rétabli avec succès.'
        );

        return $this->redirectToRoute('app_admin_users');
    }


    #[Route('/admin/user/{id}', name: 'user_profile', methods: ['GET'])]
    #[IsGranted('ROLE_MODERATOR')]
    public function profile(User $user): Response
    {
        return $this->render('admin/user/profile.html.twig', [
            'user' => $user,
        ]);
    }
    #[Route('/admin/user/{id}/edit-role', name: 'user_edit_role', methods: ['POST'])]
    #[IsGranted('ROLE_MODERATOR')]
    public function editRole(
        Request $request,
        User $user,
        EntityManagerInterface $em,
        RoleRepository $roleRepository
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_MODERATOR');

        // Vérifie le token CSRF
        $token = $request->request->get('_token');
        if (!$this->isCsrfTokenValid('edit-role-' . $user->getId(), $token)) {
            $this->addFlash('danger', 'Token CSRF invalide.');
            return $this->redirectToRoute('app_admin_users');
        }

        // ⚠️ Empêche de modifier son propre rôle
        if ($user === $this->getUser()) {
            $this->addFlash('danger', 'Vous ne pouvez pas modifier votre propre rôle.');
            return $this->redirectToRoute('app_admin_users');
        }

        // Rôles de l'utilisateur ciblé
        $targetRoles = $user->getRoles();

        // ⚠️ Empêche un modérateur de modifier un admin ou un autre modérateur
        if (
            !$this->isGranted('ROLE_ADMIN') &&
            (
                in_array('ROLE_ADMIN', $targetRoles, true) ||
                in_array('ROLE_MODERATOR', $targetRoles, true)
            )
        ) {
            $this->addFlash('danger', 'Vous ne pouvez pas modifier le rôle de cet utilisateur.');
            return $this->redirectToRoute('app_admin_users');
        }

        $newRoleName = $request->request->get('role');

        if (!in_array($newRoleName, ['ROLE_ADMIN', 'ROLE_MODERATOR', 'ROLE_USER'], true)) {
            $this->addFlash('danger', 'Rôle invalide.');
            return $this->redirectToRoute('app_admin_users');
        }

        $role = $roleRepository->findOneBy(['name' => $newRoleName]);
        if (!$role) {
            $this->addFlash('danger', 'Le rôle spécifié n\'existe pas dans la base de données.');
            return $this->redirectToRoute('app_admin_users');
        }

        $user->setRole($role);
        $em->flush();

        $this->addFlash('success', 'Rôle mis à jour avec succès.');
        return $this->redirectToRoute('app_admin_users');
    }
}
