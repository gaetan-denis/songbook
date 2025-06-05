<?php

namespace App\Controller;

use App\Entity\Chordsheet;
use App\Form\ChordsheetForm;
use App\Repository\ChordsheetRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\String\Slugger\AsciiSlugger;

#[Route('/chordsheet')]
final class ChordsheetController extends AbstractController
{
    #[Route(name: 'app_chordsheet_index', methods: ['GET'])]
    public function index(ChordsheetRepository $chordsheetRepository, Security $security): Response
    {
        $user = $security->getUser();
        if (!$user) {
            throw $this->createAccessDeniedException('Vous devez être connecté pour voir vos partitions.');
        }

        return $this->render('chordsheet/index.html.twig', [
            'chordsheets' => $chordsheetRepository->findBy(['user' => $user]),
        ]);
    }

    #[Route('/new', name: 'app_chordsheet_new', methods: ['GET'])]
    public function new(): Response
    {
        return $this->render('chordsheet/new.html.twig');
    }

    // ROUTES SPÉCIFIQUES EN PREMIER
    #[Route('/partition/save', name: 'partition_save', methods: ['POST'])]
    public function save(Request $request, EntityManagerInterface $em, Security $security): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $content = trim($data['content'] ?? '');
        $filename = trim($data['filename'] ?? '');

        $user = $security->getUser();
        if (!$user) {
            return new JsonResponse(['error' => 'Utilisateur non connecté'], 401);
        }

        // Vérification : contenu ou titre vide
        if (empty($filename)) {
            return new JsonResponse(['error' => 'Le titre ne peut pas être vide.'], 400);
        }

        if (empty($content)) {
            return new JsonResponse(['error' => 'Le contenu ne peut pas être vide.'], 400);
        }

        $partition = new Chordsheet();
        $partition->setUser($user);
        $partition->setContent($content);
        $partition->setCreatedAt(new \DateTimeImmutable());
        $partition->setTitle($filename);

        $em->persist($partition);
        $em->flush();

        return new JsonResponse([
            'success' => true,
            'id' => $partition->getId(),
            'filename' => $filename,
        ]);
    }


    // ROUTES AVEC PARAMÈTRES VARIABLES EN DERNIER
    #[Route('/chordsheet/{id}', name: 'app_chordsheet_show', methods: ['GET'])]
    public function show(Chordsheet $chordsheet, Security $security): Response
    {
        $user = $security->getUser();

        // Si la partition n'est pas publique, vérifier que l'utilisateur en est le propriétaire
        if (!$chordsheet->isPublic() && ($chordsheet->getUser() !== $user)) {
            throw $this->createAccessDeniedException('Vous ne pouvez pas accéder à cette partition.');
        }

        return $this->render('chordsheet/show.html.twig', [
            'chordsheet' => $chordsheet,
            'isOwner' => $user && $chordsheet->getUser() === $user,
            'isPublicView' => $chordsheet->isPublic() && (!$user || $chordsheet->getUser() !== $user),
        ]);
    }
    #[Route('/{id}/edit', name: 'app_chordsheet_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Chordsheet $chordsheet, EntityManagerInterface $entityManager, Security $security): Response
    {
        if ($chordsheet->getUser() !== $security->getUser()) {
            throw $this->createAccessDeniedException('Vous ne pouvez pas modifier cette partition.');
        }

        $form = $this->createForm(ChordsheetForm::class, $chordsheet);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_chordsheet_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('chordsheet/edit.html.twig', [
            'chordsheet' => $chordsheet,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_chordsheet_delete', methods: ['POST'])]
    public function delete(Request $request, Chordsheet $chordsheet, EntityManagerInterface $entityManager, Security $security): Response
    {
        if ($chordsheet->getUser() !== $security->getUser()) {
            throw $this->createAccessDeniedException('Vous ne pouvez pas supprimer cette partition.');
        }

        if ($this->isCsrfTokenValid('delete'.$chordsheet->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($chordsheet);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_chordsheet_index', [], Response::HTTP_SEE_OTHER);
    }

    // src/Controller/ChordsheetController.php
    #[Route('/chordsheet/{id}/export', name: 'app_chordsheet_export', methods: ['GET'])]
    public function export(Chordsheet $chordsheet): Response
    {
        $slugger = new AsciiSlugger();
        $safeTitle = $slugger->slug($chordsheet->getTitle())->lower();
        $filename = $safeTitle . '.chordpro';

        return new Response(
            $chordsheet->getContent(), // contenu en format ChordPro
            Response::HTTP_OK,
            [
                'Content-Type' => 'text/plain',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ]
        );
    }

    // Ajoutez cette route dans votre ChordsheetController.php

    #[Route('/{id}/update', name: 'chordsheet_update', methods: ['POST'])]
    public function update(Request $request, Chordsheet $chordsheet, EntityManagerInterface $em, Security $security): JsonResponse
    {
        // Vérification de sécurité
        if ($chordsheet->getUser() !== $security->getUser()) {
            return new JsonResponse(['error' => 'Accès non autorisé'], 403);
        }

        $data = json_decode($request->getContent(), true);

        $content = trim($data['content'] ?? '');
        $filename = trim($data['filename'] ?? '');

        // Vérification : contenu ou titre vide (comme dans la méthode save)
        if (empty($filename)) {
            return new JsonResponse(['error' => 'Le titre ne peut pas être vide.'], 400);
        }

        if (empty($content)) {
            return new JsonResponse(['error' => 'Le contenu ne peut pas être vide.'], 400);
        }

        // Mise à jour de la partition
        $chordsheet->setContent($content);
        $chordsheet->setTitle($filename);

        $em->flush();

        return new JsonResponse([
            'success' => true,
            'id' => $chordsheet->getId(),
            'filename' => $chordsheet->getTitle()
        ]);
    }

    #[Route('/{id}/toggle-public', name: 'app_chordsheet_toggle_public', methods: ['POST'])]
    public function togglePublic(Request $request, Chordsheet $chordsheet, EntityManagerInterface $entityManager, Security $security): JsonResponse
    {
        // Vérification de sécurité
        if ($chordsheet->getUser() !== $security->getUser()) {
            return new JsonResponse(['error' => 'Accès non autorisé'], 403);
        }

        // Vérification du token CSRF pour la sécurité
        if (!$this->isCsrfTokenValid('toggle_public'.$chordsheet->getId(), $request->getPayload()->getString('_token'))) {
            return new JsonResponse(['error' => 'Token CSRF invalide'], 400);
        }

        $wasPublic = $chordsheet->isPublic();

        // Basculer le statut public/privé
        $chordsheet->setIsPublic(!$wasPublic);

        // Si on publie la partition (passage de privé à public)
        if (!$wasPublic && $chordsheet->isPublic()) {
            $chordsheet->setPublishedAt(new \DateTimeImmutable());
        }
        // Si on rend privée une partition qui était publique, on peut garder la date de publication
        // ou la supprimer selon vos besoins. Ici je la garde pour l'historique

        $entityManager->flush();

        return new JsonResponse([
            'success' => true,
            'isPublic' => $chordsheet->isPublic(),
            'message' => $chordsheet->isPublic() ? 'Partition publiée avec succès' : 'Partition rendue privée'
        ]);
    }

    #[Route('/library', name: 'app_chordsheet_library', methods: ['GET'])]
    public function library(ChordsheetRepository $chordsheetRepository, Security $security): Response
    {
        // Récupérer toutes les partitions publiques, triées par date de publication (plus récente en premier)
        $publicChordsheets = $chordsheetRepository->findBy(
            ['isPublic' => true],
            ['publishedAt' => 'DESC', 'createdAt' => 'DESC'] // Fallback sur createdAt si publishedAt est null
        );

        return $this->render('chordsheet/library.html.twig', [
            'chordsheets' => $publicChordsheets,
            'currentUser' => $security->getUser(),
        ]);
    }

    #[Route('/library/{id}/export', name: 'app_chordsheet_library_export', methods: ['GET'])]
    public function libraryExport(Chordsheet $chordsheet, Security $security): Response
    {
        // Vérifier que la partition est publique
        if (!$chordsheet->isPublic()) {
            throw $this->createAccessDeniedException('Cette partition n\'est pas publique.');
        }

        // Vérifier que l'utilisateur est connecté
        if (!$security->getUser()) {
            throw $this->createAccessDeniedException('Vous devez être connecté pour télécharger une partition.');
        }

        $slugger = new AsciiSlugger();
        $safeTitle = $slugger->slug($chordsheet->getTitle())->lower();
        $filename = $safeTitle . '.chordpro';

        return new Response(
            $chordsheet->getContent(),
            Response::HTTP_OK,
            [
                'Content-Type' => 'text/plain',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ]
        );
    }

    #[Route('/library/{id}/delete', name: 'app_chordsheet_library_delete', methods: ['POST'])]
    public function libraryDelete(Request $request, Chordsheet $chordsheet, EntityManagerInterface $entityManager, Security $security): Response
    {
        $user = $security->getUser();

        // Vérifier que l'utilisateur est connecté
        if (!$user) {
            throw $this->createAccessDeniedException('Vous devez être connecté.');
        }

        // Vérifier que l'utilisateur est admin
        if (!$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException('Seuls les administrateurs peuvent supprimer des partitions de la bibliothèque.');
        }

        // Vérifier que la partition est publique
        if (!$chordsheet->isPublic()) {
            throw $this->createAccessDeniedException('Cette partition n\'est pas publique.');
        }

        if ($this->isCsrfTokenValid('delete_library'.$chordsheet->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($chordsheet);
            $entityManager->flush();

            $this->addFlash('success', 'Partition supprimée avec succès de la bibliothèque.');
        }

        return $this->redirectToRoute('app_chordsheet_library', [], Response::HTTP_SEE_OTHER);
    }
}