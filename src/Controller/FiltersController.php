<?php

namespace App\Controller;

use App\Repository\CreationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class FiltersController extends AbstractController
{
    #[Route('/all-creations', name: 'all.creations')]
    public function creationList(CreationRepository $creationRepo): Response
    {
        $creations = $creationRepo->search(null, 'all');
        return $this->render('frontOffice/creation/_creation_list.html.twig', [
            'creations' => $creations
        ]);
    }

    #[Route('/creations/search', name: 'creations.search')]
    public function searchCreationsByName(Request $request, CreationRepository $creationRepo): Response
    {
        $query = $request->query->get('q');
        $theme = $request->query->get('theme');
        $creations = $creationRepo->search($query, $theme);

        return $this->render('frontOffice/creation/_creation_list.html.twig', [
            'creations' => $creations
        ]);
    }

    #[Route('/creations/search-by-theme', name: 'creations.search_by_theme')]
    public function searchCreationsByTheme(Request $request, CreationRepository $creationRepo): Response
    {
        $theme = $request->query->get('theme');
        $creations = $creationRepo->search(null, $theme);

        return $this->render('frontOffice/creation/_creation_list.html.twig', [
            'creations' => $creations
        ]);
    }
}
