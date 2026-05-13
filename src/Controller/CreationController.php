<?php

namespace App\Controller;

use App\Entity\Creation;
use App\Form\CreationType;
use App\Repository\CreationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/creations')]
final class CreationController extends AbstractController
{
    #[Route(name: 'app.creation.index', methods: ['GET'])]
    public function index(CreationRepository $creationRepository): Response
    {
        return $this->render('frontOffice/creation/index.html.twig', [
            'creations' => $creationRepository->findAll(),
        ]);
    }

    #[Route('/nouvelle-creation', name: 'app.creation.new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $creation = new Creation();
        $form = $this->createForm(CreationType::class, $creation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($creation);
            $entityManager->flush();

            return $this->redirectToRoute('app.creation.index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('backOffice/creation/new.html.twig', [
            'creation' => $creation,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/details', name: 'app.creation.show', methods: ['GET'])]
    public function show(Creation $creation): Response
    {
        return $this->render('frontOffice/creation/show.html.twig', [
            'creation' => $creation,
        ]);
    }

    #[Route('/{id}/modifier', name: 'app.creation.edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Creation $creation, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(CreationType::class, $creation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app.creation.index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('backOffice/creation/edit.html.twig', [
            'creation' => $creation,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/supprimer', name: 'app.creation.delete', methods: ['POST'])]
    public function delete(Request $request, Creation $creation, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$creation->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($creation);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app.creation.index', [], Response::HTTP_SEE_OTHER);
    }
}
