<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ErrorController extends AbstractController
{
    #[Route('/{any}', name: 'error_404', requirements: ['any' => '.*'], priority: -100)]
    public function notFound(): Response
    {
        return $this->render('error/index.html.twig', [], new Response('', 404));
    }
}
