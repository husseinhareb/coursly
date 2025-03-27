<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\CoursRepository;

final class CoursController extends AbstractController
{
    #[Route('/cours', name: 'cours.index')]
    public function index(Request $request, CoursRepository $repository): Response
    {
        $cours = $repository->findAll();

        // Liste de couleurs définies pour varier les cartes
        $colors = ['#3498db', '#e74c3c', '#2ecc71', '#f39c12', '#9b59b6', '#1abc9c', '#d35400', '#8e44ad'];

        return $this->render('cours/index.html.twig', [
            'cours' => $cours,
            'colors' => $colors,
        ]);
    }

    #[Route('/cours/{slug}-{id}', name: 'cours.show', requirements: ['slug' => '[a-z0-9\-]+'])]
    public function show(string $slug, int $id, CoursRepository $repository): Response
    {  
        $cours = $repository->find($id);
        
        if (!$cours) {
            throw $this->createNotFoundException('Le cours demandé n\'existe pas.');
        }

        if ($cours->getSlug() !== $slug) {
            return $this->redirectToRoute('cours.show', [
                'slug' => $cours->getSlug(),
                'id' => $cours->getId()
            ], 301);
        }
        
        return $this->render('cours/show.html.twig', [
            'cours' => $cours
        ]);
    }
    
    #[Route('/search-cours', name: 'search_cours', methods: ['GET'])]
    public function searchCours(Request $request, CoursRepository $coursRepository): JsonResponse
    {
        $term = $request->query->get('q', '');

        if (empty($term)) {
            return $this->json([]);
        }

        $cours = $coursRepository->searchCours($term);

        $data = array_map(fn($cours) => [
            'id' => $cours->getId(),
            'titre' => $cours->getTitre(),
            'slug' => $cours->getSlug(),
        ], $cours);

        return $this->json($data);
    }
}
