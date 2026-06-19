<?php

namespace App\Controller;

use App\Entity\Theme;
use App\Repository\ThemeRepository;
use App\Form\ThemeType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/themes')]
final class ThemeController extends AbstractController
{
    public function __construct()
    {
    }
    
    #[Route(name: 'app.theme.index', methods: ['GET'])]
    public function index(ThemeRepository $themeRepository): Response
    {
        $themes = $themeRepository->findAll();
        return $this->render('backOffice/theme/index.html.twig', [
            'themes' => $themes,
        ]);
    }
    
    #[Route('/new', name: 'app.theme.new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em, SluggerInterface $slugger): Response
    {
        $theme = new Theme();
        $form = $this->createForm(ThemeType::class, $theme);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $themeName = $theme->getName();
            $theme->setSlug($slugger->slug($themeName)->lower());
            $em->persist($theme);
            $em->flush();
            $this->addFlash('success', 'Le thème a bien été ajouté.');
            return $this->redirectToRoute('app_theme_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('backOffice/theme/new.html.twig', [
            'theme' => $theme,
            'form' => $form,
        ]);
    }
    
    #[Route('/{id}/edit', name: 'app.theme.edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Theme $theme, EntityManagerInterface $em, SluggerInterface $slugger): Response
    {
        $form = $this->createForm(ThemeType::class, $theme);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $themeName = $theme->getName();
            $theme->setSlug($slugger->slug($themeName)->lower());
            $em->persist($theme);
            $em->flush();
            $this->addFlash('success', 'Le thème a bien été modifié.');
            return $this->redirectToRoute('app.theme.index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('backOffice/theme/edit.html.twig', [
            'theme' => $theme,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/supprimer', name: 'app.theme.delete', methods: ['POST'])]
    public function delete(Request $request, Theme $theme, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete'.$theme->getId(), $request->getPayload()->getString('_token'))) {
            $em->remove($theme);
            $em->flush();
            $this->addFlash('success', 'Le thème a bien été supprimé.');
        } else {
            $this->addFlash('error', 'Token invalide, la suppression a échoué.');
        }

        return $this->redirectToRoute('app.theme.index', [], Response::HTTP_SEE_OTHER);
    }
}
