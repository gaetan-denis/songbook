<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class LegalController extends AbstractController
{
    #[Route('/legal', name: 'app_legal')]
    public function index(): Response
    {
        return $this->render('legal/index.html.twig', [
            'controller_name' => 'LegalController',
        ]);
    }

    #[Route('/privacy-policy', name: 'app_privacy_policy')]
    public function privacyPolicy(): Response
    {
        return $this->render('legal/privacy_policy.html.twig');
    }

    #[Route('/mentions-legales', name: 'app_legal_notice')]
    public function legalNotice(ParameterBagInterface $params): Response
    {
        return $this->render('legal/legal_notice.html.twig', [
            'owner_name' => $params->get('legal.owner_name'),
            'owner_address' => $params->get('legal.owner_address'),
            'contact_email' => $params->get('legal.contact_email'),
        ]);
    }
}
