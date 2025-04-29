<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

class SecurityController extends AbstractController
{
    private RateLimiterFactory $loginLimiter;

    // Injection du RateLimiterFactory via le constructeur
    public function __construct(RateLimiterFactory $loginLimiter)
    {
        $this->loginLimiter = $loginLimiter;
    }

    #[Route(path: '/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils, Request $request): Response
    {
        // Créer un rate limiter basé sur l'adresse IP du client
        $limiter = $this->loginLimiter->create($request->getClientIp());
        $limit = $limiter->consume();

        // Si les tentatives sont épuisées, on lance une exception ou un message d'erreur
        if (!$limit->isAccepted()) {
            return $this->render('security/too_many_attempts.html.twig');
        }

        // Récupérer les erreurs de connexion (si présentes)
        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        // Cette méthode est interceptée par Symfony et ne doit rien faire ici
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}
