<?php

// AdminController.php
namespace App\Controller;

use App\Entity\Chordsheet;
use App\Entity\User;
use App\Repository\ChordsheetRepository;
use App\Repository\RoleRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

//

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
    public function managePartitions(ChordsheetRepository $chordsheetRepository): Response
    {
        // Récupérer UNIQUEMENT les partitions publiques triées par date de création
        $partitions = $chordsheetRepository->findBy(['isPublic' => true], ['createdAt' => 'DESC']);

        // Calculer le nombre d'utilisateurs uniques (uniquement pour les partitions publiques)
        $uniqueUserIds = [];
        foreach ($partitions as $partition) {
            if ($partition->getUser()) { // Vérification de sécurité
                $uniqueUserIds[$partition->getUser()->getId()] = true;
            }
        }
        $uniqueUsersCount = count($uniqueUserIds);

        return $this->render('admin/partitions.html.twig', [
            'partitions' => $partitions,
            'uniqueUsersCount' => $uniqueUsersCount,
        ]);
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
        Request                $request,
        User                   $user,
        EntityManagerInterface $em,
        RoleRepository         $roleRepository,
        SessionInterface       $session
    ): Response
    {
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

        $currentUser = $this->getUser();
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

        // 🔥 LOGIQUE DE SUCCESSION ADMINISTRATIVE
        if ($newRoleName === 'ROLE_ADMIN' && $this->isGranted('ROLE_ADMIN')) {
            // Vérifier si l'admin veut vraiment transférer ses privilèges
            $confirmed = $request->request->get('admin_succession_confirmed');

            if (!$confirmed) {
                // Première tentative - demander confirmation
                $this->addFlash('warning',
                    sprintf(
                        'ATTENTION : En nommant %s administrateur, vous serez automatiquement rétrogradé au rang d\'utilisateur standard. Cette action est DÉFINITIVE et IMMÉDIATE. Confirmez pour continuer.',
                        $user->getUsername()
                    )
                );
                return $this->redirectToRoute('app_admin_users', [
                    'confirm_admin_transfer' => $user->getId()
                ]);
            }

            // Succession confirmée - procéder au transfert
            $role = $roleRepository->findOneBy(['name' => $newRoleName]);
            $userRole = $roleRepository->findOneBy(['name' => 'ROLE_USER']);

            if (!$role || !$userRole) {
                $this->addFlash('danger', 'Erreur lors de la récupération des rôles.');
                return $this->redirectToRoute('app_admin_users');
            }

            // Transaction pour s'assurer que les deux changements se font
            $em->beginTransaction();
            try {
                // 1. Promouvoir le nouvel admin
                $user->setRole($role);

                // 2. Rétrograder l'ancien admin
                $currentUser->setRole($userRole);

                $em->flush();
                $em->commit();

                // 3. Invalider la session de l'ancien admin pour forcer la reconnexion
                $session->invalidate();

                $this->addFlash('success',
                    sprintf(
                        'Succession administrative effectuée. %s est maintenant administrateur. Vous avez été déconnecté avec succès.',
                        $user->getUsername()
                    )
                );

                // Rediriger vers la page de connexion
                return $this->redirectToRoute('app_login');
            } catch (\Exception $e) {
                $em->rollback();
                $this->addFlash('danger', 'Erreur lors de la succession administrative. Aucun changement effectué.');
            }

            return $this->redirectToRoute('app_admin_users');
        }

        // Logique normale pour les autres changements de rôle
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

    /**
     * Méthode helper pour gérer la déconnexion forcée d'un utilisateur
     * (utile si on veut étendre cette fonctionnalité à d'autres cas)
     */
    private function forceUserLogout(User $user, SessionInterface $session): void
    {
        // Si l'utilisateur modifié est l'utilisateur courant, invalider sa session
        if ($user === $this->getUser()) {
            $session->invalidate();
        }

        // Note: Pour déconnecter d'autres utilisateurs, il faudrait une approche plus complexe
        // impliquant un système de gestion de sessions centralisé
    }
    #[Route('/admin/partition/{id}/delete', name: 'admin_partition_delete', methods: ['POST'])]
    #[IsGranted('ROLE_MODERATOR')]
    public function deletePartition(Chordsheet $chordsheet, Request $request, EntityManagerInterface $em): Response
    {
        // Vérification du token CSRF
        if (!$this->isCsrfTokenValid('delete-partition-' . $chordsheet->getId(), $request->request->get('_token'))) {
            $this->addFlash('error', 'Token CSRF invalide.');
            return $this->redirectToRoute('app_admin_partitions');
        }

        try {
            $partitionTitle = $chordsheet->getTitle();
            $em->remove($chordsheet);
            $em->flush();

            $this->addFlash('success', 'Partition "' . $partitionTitle . '" supprimée avec succès.');
        } catch (\Exception $e) {
            $this->addFlash('error', 'Erreur lors de la suppression de la partition.');
        }

        return $this->redirectToRoute('app_admin_partitions');
    }

// Nouvelle méthode pour voir les détails d'une partition
    #[Route('/admin/partition/{id}', name: 'admin_partition_show', methods: ['GET'])]
    #[IsGranted('ROLE_MODERATOR')]
    public function showPartition(Chordsheet $chordsheet): Response
    {
        return $this->render('admin/partition/show.html.twig', [
            'chordsheet' => $chordsheet,
        ]);
    }
}