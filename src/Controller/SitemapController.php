<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class SitemapController extends AbstractController
{
    #[Route('/sitemap.xml', name: 'app.sitemap', defaults: ['_format' => 'xml'])]
    public function index(): Response
    {
        // Pages publiques indexables
        $urls = [
            [
                'loc'        => $this->generateUrl('app.home', [], UrlGeneratorInterface::ABSOLUTE_URL),
                'lastmod'    => null,
                'changefreq' => 'weekly',
                'priority'   => '1.0',
            ],
            [
                'loc'        => $this->generateUrl('app.creation.index', [], UrlGeneratorInterface::ABSOLUTE_URL),
                'lastmod'    => null,
                'changefreq' => 'weekly',
                'priority'   => '0.9',
            ],
            [
                'loc'        => $this->generateUrl('app.contact', [], UrlGeneratorInterface::ABSOLUTE_URL),
                'lastmod'    => null,
                'changefreq' => 'monthly',
                'priority'   => '0.7',
            ],
            [
                'loc'        => $this->generateUrl('app.localisation', [], UrlGeneratorInterface::ABSOLUTE_URL),
                'lastmod'    => null,
                'changefreq' => 'monthly',
                'priority'   => '0.6',
            ],
        ];

        $response = new Response(
            $this->renderView('sitemap/sitemap.xml.twig', ['urls' => $urls]),
            Response::HTTP_OK,
            ['Content-Type' => 'application/xml']
        );

        // Cache public de 24h
        $response->setPublic();
        $response->setMaxAge(86400);

        return $response;
    }
}
