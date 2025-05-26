<?php

namespace App\Controller;

use App\Entity\Songbook;
use App\Form\SongbookForm;
use App\Repository\SongbookRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/songbook')]
final class SongbookController extends AbstractController
{
    #[Route('', name: 'app_songbook_index', methods: ['GET'])]
    public function index(SongbookRepository $songbookRepository): Response
    {
        $user = $this->getUser();

        return $this->render('songbook/index.html.twig', [
            'songbooks' => $songbookRepository->findBy(['user' => $user]),
        ]);
    }

    #[Route('/new', name: 'app_songbook_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $songbook = new Songbook();
        $form = $this->createForm(SongbookForm::class, $songbook);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // 👇 On assigne l'utilisateur connecté
            $songbook->setUser($this->getUser());

            $entityManager->persist($songbook);
            $entityManager->flush();

            return $this->redirectToRoute('app_songbook_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('songbook/new.html.twig', [
            'songbook' => $songbook,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_songbook_show', methods: ['GET'])]
    public function show(Songbook $songbook): Response
    {
        return $this->render('songbook/show.html.twig', [
            'songbook' => $songbook,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_songbook_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Songbook $songbook, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(SongbookForm::class, $songbook);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_songbook_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('songbook/edit.html.twig', [
            'songbook' => $songbook,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_songbook_delete', methods: ['POST'])]
    public function delete(Request $request, Songbook $songbook, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$songbook->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($songbook);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_songbook_index', [], Response::HTTP_SEE_OTHER);
    }
}
