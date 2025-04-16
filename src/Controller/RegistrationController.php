<?php

namespace App\Controller;

use App\Entity\Role;
use App\Entity\User;
use App\Form\RegistrationFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class RegistrationController extends AbstractController
{
    #[Route('/register', name: 'app_register')]
    public function register(
        Request $request,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher
    ): Response {
        $user = new User();

        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Récupère le mot de passe en clair
            $plainPassword = $form->get('plainPassword')->getData();

            // Hash le mot de passe
            $hashedPassword = $passwordHasher->hashPassword($user, $plainPassword);
            $user->setPassword($hashedPassword);

            // Attribue un rôle par défaut (si tu as une entité Role)
            // Ou simplement : $user->setRoles(['ROLE_USER']);

            $roleRepository = $entityManager->getRepository(Role::class);
            $defaultRole = $roleRepository->findOneBy(['name' => 'ROLE_USER']);

            if ($defaultRole) {
                $user->setRole($defaultRole);
            } else {
                // en cas de souci, tu peux lever une exception ou définir un rôle de secours
                throw new \Exception("Le rôle ROLE_USER n'existe pas en base.");
            }

            // Enregistrement en base de données
            $entityManager->persist($user);
            $entityManager->flush();

            // Redirection après inscription réussie
            return $this->redirectToRoute('app_login');
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }
}

