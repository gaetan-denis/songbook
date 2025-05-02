<?php

// AdminController.php
namespace App\Controller;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;  // Utilise Annotation pour la route
use Symfony\Component\Security\Http\Attribute\IsGranted;

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
}
