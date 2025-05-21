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
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

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

    #[Route('/profile/export', name: 'app_profile_export')]
    #[IsGranted("ROLE_USER")]
    public function export(): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        // Exemple de structure des données à exporter
        $data = [
            'id' => $user->getId(),
            'email' => $user->getEmail(),
            'username' => $user->getUsername(), // Plutôt que getUserIdentifier()
            'roles' => $user->getRoles(),
            'created_at' => $user->getCreatedAt()?->format('Y-m-d H:i:s'),
            'last_connection' => $user->getLastConnection()?->format('Y-m-d H:i:s'),
            'avatar_url' => $user->getAvatarUrl(),
            'is_active' => $user->isActive(),
            'is_banned' => $user->getIsBanned(),
            'terms_accepted_at' => $user->getTermsAcceptedAt()?->format('Y-m-d H:i:s'),
        ];

        // Encodage en JSON
        $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        // Création de la réponse en pièce jointe
        $response = new Response($json);
        $disposition = $response->headers->makeDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            'my_data_export.json'
        );
        $response->headers->set('Content-Disposition', $disposition);
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

}

