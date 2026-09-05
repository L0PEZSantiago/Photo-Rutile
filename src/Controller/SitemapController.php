<?php

namespace App\Controller;

use App\Repository\CreationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class SitemapController extends AbstractController
{
    #[Route('/sitemap.xml', name: 'app.sitemap', defaults: ['_format' => 'xml'])]
    public function index(CreationRepository $creationRepository): Response
    {
        // Pages statiques publiques
        $staticUrls = [
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

        // Pages dynamiques : détail de chaque création publiée
        $creations = $creationRepository->findBy(['isPublished' => true]);
        $dynamicUrls = [];

        foreach ($creations as $creation) {
            $lastmod = $creation->getUpdatedAt() ?? $creation->getCreatedAt();

            $dynamicUrls[] = [
                'loc'        => $this->generateUrl('app.creation.show', ['id' => $creation->getId()], UrlGeneratorInterface::ABSOLUTE_URL),
                'lastmod'    => $lastmod?->format('Y-m-d'),
                'changefreq' => 'monthly',
                'priority'   => '0.8',
            ];
        }

        $urls = array_merge($staticUrls, $dynamicUrls);

        $response = new Response(
            $this->renderView('sitemap/sitemap.xml.twig', ['urls' => $urls]),
            Response::HTTP_OK,
            ['Content-Type' => 'application/xml']
        );

        // Cache public de 24h pour éviter de requêter la BDD à chaque visite de bot
        $response->setPublic();
        $response->setMaxAge(86400);

        return $response;
    }
}
