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
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

final class RegistrationController extends AbstractController
{
    private $params;

    public function __construct(ParameterBagInterface $params)
    {
        $this->params = $params;
    }
    #[Route('/register', name: 'app_register')]
    public function register(
        Request $request,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher
    ): Response {
        $user = new User();

        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        // --- INSÉRER LA VÉRIFICATION HCAPTCHA ICI ---
        if ($form->isSubmitted()) {
            $hCaptchaResponse = $request->request->get('h-captcha-response');
            $secret = $this->params->get('HCAPTCHA_SECRET_KEY');

            $response = file_get_contents('https://hcaptcha.com/siteverify?secret=' . $secret . '&response=' . $hCaptchaResponse);
            $responseKeys = json_decode($response, true);

            if (!$responseKeys['success']) {
                $this->addFlash('error', 'Le CAPTCHA a échoué. Veuillez réessayer.');
                return $this->redirectToRoute('app_register');
            }
        }
        // --- FIN DE LA VÉRIFICATION HCAPTCHA ---

        if ($form->isSubmitted() && $form->isValid()) {
            // Récupère le mot de passe en clair
            $plainPassword = $form->get('plainPassword')->getData();

            // Hash le mot de passe
            $hashedPassword = $passwordHasher->hashPassword($user, $plainPassword);
            $user->setPassword($hashedPassword);

            // Attribue un rôle par défaut (si tu as une entité Role)
            $roleRepository = $entityManager->getRepository(Role::class);
            $defaultRole = $roleRepository->findOneBy(['name' => 'ROLE_USER']);

            if ($defaultRole) {
                $user->setRole($defaultRole);
            } else {
                throw new \Exception("Le rôle ROLE_USER n'existe pas en base.");
            }

            // Vérification de l'unicité de l'email
            $userRepository = $entityManager->getRepository(User::class);
            $existingUserByEmail = $userRepository->findOneBy(['email' => $user->getEmail()]);

            if ($existingUserByEmail) {
                $this->addFlash('error', 'Cet email est déjà utilisé. Veuillez en choisir un autre.');
                return $this->redirectToRoute('app_register');
            }

            // Vérification de l'unicité du username
            $existingUserByUsername = $userRepository->findOneBy(['username' => $user->getUsername()]);

            if ($existingUserByUsername) {
                $this->addFlash('error', 'Ce nom d\'utilisateur est déjà pris. Veuillez en choisir un autre.');
                return $this->redirectToRoute('app_register');
            }

            $user->setTermsAcceptedAt(new \DateTimeImmutable());

            // Enregistrement en base de données
            $entityManager->persist($user);
            $entityManager->flush();

            // Redirection après inscription réussie
            return $this->redirectToRoute('app_login');
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
            'hcaptcha_site_key' => $this->params->get('HCAPTCHA_SITE_KEY'),
        ]);
    }

}