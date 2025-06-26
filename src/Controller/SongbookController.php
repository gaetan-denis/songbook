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
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Entity\SongbookChordsheet;
use App\Repository\ChordsheetRepository;
use Symfony\Component\HttpFoundation\JsonResponse;

#[Route('/songbook')]
#[IsGranted('ROLE_USER')]
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
            $this->addFlash('success', 'Le songbook a bien été créé.');

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
        if ($songbook->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException('Vous ne pouvez pas accéder à ce songbook.');
        }
        return $this->render('songbook/show.html.twig', [
            'songbook' => $songbook,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_songbook_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Songbook $songbook, EntityManagerInterface $entityManager): Response
    {
        if ($songbook->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException('Vous ne pouvez pas accéder à ce songbook.');
        }
        $form = $this->createForm(SongbookForm::class, $songbook);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'Le songbook a bien été mis à jour.');

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
        if ($songbook->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException('Vous ne pouvez pas accéder à ce songbook.');
        }

        if ($this->isCsrfTokenValid('delete'.$songbook->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($songbook);
            $entityManager->flush();
            $this->addFlash('danger', 'Le songbook a bien été supprimé.');
        }

        return $this->redirectToRoute('app_songbook_index', [], Response::HTTP_SEE_OTHER);
    }
    /**
     * Ajouter une partition à un recueil
     */
    #[Route('/{id}/add-chordsheet/{chordsheetId}', name: 'app_songbook_add_chordsheet', methods: ['POST'])]
    public function addChordsheet(
        Request $request,
        Songbook $songbook,
        int $chordsheetId,
        ChordsheetRepository $chordsheetRepository,
        EntityManagerInterface $entityManager
    ): Response {
        // Vérification de propriété du songbook
        if ($songbook->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException('Vous ne pouvez pas modifier ce songbook.');
        }

        // Vérification CSRF
        if (!$this->isCsrfTokenValid('add_chordsheet_'.$songbook->getId(), $request->getPayload()->getString('_token'))) {
            $this->addFlash('error', 'Token de sécurité invalide.');
            return $this->redirectToRoute('app_songbook_show', ['id' => $songbook->getId()]);
        }

        // Récupération de la partition
        $chordsheet = $chordsheetRepository->find($chordsheetId);
        if (!$chordsheet) {
            $this->addFlash('error', 'Partition introuvable.');
            return $this->redirectToRoute('app_songbook_show', ['id' => $songbook->getId()]);
        }

        // Vérification que l'utilisateur peut accéder à cette partition
        if (!$chordsheet->isPublic() && $chordsheet->getUser() !== $this->getUser()) {
            $this->addFlash('error', 'Vous ne pouvez pas ajouter cette partition.');
            return $this->redirectToRoute('app_songbook_show', ['id' => $songbook->getId()]);
        }

        // Calcul de la prochaine position
        $maxPosition = $entityManager->createQuery(
            'SELECT MAX(sc.position) FROM App\Entity\SongbookChordsheet sc WHERE sc.songbook = :songbook'
        )
            ->setParameter('songbook', $songbook)
            ->getSingleScalarResult();

        $nextPosition = ($maxPosition ?? 0) + 1;

        // Création de la relation
        $songbookChordsheet = new SongbookChordsheet();
        $songbookChordsheet->setSongbook($songbook);
        $songbookChordsheet->setChordsheet($chordsheet);
        $songbookChordsheet->setPosition($nextPosition);

        $entityManager->persist($songbookChordsheet);
        $entityManager->flush();

        $this->addFlash('success', 'La partition "' . $chordsheet->getTitle() . '" a été ajoutée au recueil.');

        return $this->redirectToRoute('app_songbook_show', ['id' => $songbook->getId()]);
    }

    /**
     * Supprimer une partition d'un recueil
     */
    #[Route('/{id}/remove-chordsheet/{songbookChordsheetId}', name: 'app_songbook_remove_chordsheet', methods: ['POST'])]
    public function removeChordsheet(
        Request $request,
        Songbook $songbook,
        int $songbookChordsheetId,
        EntityManagerInterface $entityManager
    ): Response {
        // Vérification de propriété du songbook
        if ($songbook->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException('Vous ne pouvez pas modifier ce songbook.');
        }

        // Vérification CSRF
        if (!$this->isCsrfTokenValid('remove_chordsheet_'.$songbook->getId(), $request->getPayload()->getString('_token'))) {
            $this->addFlash('error', 'Token de sécurité invalide.');
            return $this->redirectToRoute('app_songbook_show', ['id' => $songbook->getId()]);
        }

        // Récupération de la relation SongbookChordsheet
        $songbookChordsheet = $entityManager->getRepository(SongbookChordsheet::class)->find($songbookChordsheetId);

        if (!$songbookChordsheet || $songbookChordsheet->getSongbook() !== $songbook) {
            $this->addFlash('error', 'Relation introuvable.');
            return $this->redirectToRoute('app_songbook_show', ['id' => $songbook->getId()]);
        }

        $chordsheetTitle = $songbookChordsheet->getChordsheet()->getTitle();

        $entityManager->remove($songbookChordsheet);
        $entityManager->flush();

        $this->addFlash('success', 'La partition "' . $chordsheetTitle . '" a été supprimée du recueil.');

        return $this->redirectToRoute('app_songbook_show', ['id' => $songbook->getId()]);
    }

    /**
     * Afficher les partitions disponibles pour ajout (modal ou page séparée)
     */
    #[Route('/{id}/available-chordsheets', name: 'app_songbook_available_chordsheets', methods: ['GET'])]
    public function availableChordsheets(
        Songbook $songbook,
        ChordsheetRepository $chordsheetRepository
    ): Response {
        // Vérification de propriété du songbook
        if ($songbook->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException('Vous ne pouvez pas accéder à ce songbook.');
        }

        // Récupération des partitions disponibles (publiques + celles de l'utilisateur)
        $availableChordsheets = $chordsheetRepository->findAvailableForUser($this->getUser());

        return $this->render('songbook/available_chordsheets.html.twig', [
            'songbook' => $songbook,
            'chordsheets' => $availableChordsheets,
        ]);
    }

    /**
     * Réorganiser les positions des partitions dans un recueil
     */
    #[Route('/{id}/reorder', name: 'app_songbook_reorder', methods: ['POST'])]
    public function reorderChordsheets(
        Request $request,
        Songbook $songbook,
        EntityManagerInterface $entityManager
    ): JsonResponse {
        // Vérification de propriété du songbook
        if ($songbook->getUser() !== $this->getUser()) {
            return new JsonResponse(['error' => 'Accès refusé'], 403);
        }

        // Vérification CSRF
        if (!$this->isCsrfTokenValid('reorder_'.$songbook->getId(), $request->getPayload()->getString('_token'))) {
            return new JsonResponse(['error' => 'Token invalide'], 400);
        }

        $positions = $request->getPayload()->all('positions'); // Array [songbookChordsheetId => position]

        foreach ($positions as $songbookChordsheetId => $position) {
            $songbookChordsheet = $entityManager->getRepository(SongbookChordsheet::class)->find($songbookChordsheetId);

            if ($songbookChordsheet && $songbookChordsheet->getSongbook() === $songbook) {
                $songbookChordsheet->setPosition((int)$position);
            }
        }

        $entityManager->flush();

        return new JsonResponse(['success' => true]);
    }

}
