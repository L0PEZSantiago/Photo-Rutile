<?php

namespace App\Controller;

use App\Repository\CreationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app.home')]
    public function index(CreationRepository $cr): Response
    {
        $highlightedCreations = $cr->findHighlightedCreations();
        return $this->render('frontOffice/home/index.html.twig', [
            'highlightedCreations' => $highlightedCreations,
        ]);
    }
    
    #[Route('/contact', name: 'app.contact')]
    public function contact(): Response
    {
        return $this->render('frontOffice/contact/index.html.twig');
    }

    #[Route('/contact/merci', name: 'app.contact_confirmation')]
    public function contactConfirmation(): Response
    {
        return $this->render('frontOffice/contact/confirmation.html.twig');
    }

    #[Route('/localisation', name: 'app.localisation')]
    public function localisation(): Response
    {
        return $this->render('frontOffice/localisation/index.html.twig');
    }
}
