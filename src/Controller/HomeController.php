<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app.home')]
    public function index(): Response
    {
        return $this->render('frontOffice/home/index.html.twig');
    }
    
    #[Route('/contact', name: 'app.contact')]
    public function contact(): Response
    {
        return $this->render('frontOffice/contact/index.html.twig');
    }

    #[Route('/localisation', name: 'app.localisation')]
    public function localisation(): Response
    {
        return $this->render('frontOffice/localisation/index.html.twig');
    }
}
