<?php

namespace App\Controller;

use App\Entity\StudyClass;
use App\Form\StudyClassType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/study-class')]
class StudyClassController extends AbstractController
{
    #[Route('/', name: 'app_study_class_index', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $studyClasses = $entityManager
            ->getRepository(StudyClass::class)
            ->findAll();

        return $this->render('study_class/index.html.twig', [
            'study_classes' => $studyClasses,
        ]);
    }

    #[Route('/new', name: 'app_study_class_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $studyClass = new StudyClass();
        $form = $this->createForm(StudyClassType::class, $studyClass);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($studyClass);
            $entityManager->flush();

            return $this->redirectToRoute('app_study_class_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('study_class/new.html.twig', [
            'study_class' => $studyClass,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_study_class_show', methods: ['GET'])]
    public function show(StudyClass $studyClass): Response
    {
        return $this->render('study_class/show.html.twig', [
            'study_class' => $studyClass,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_study_class_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, StudyClass $studyClass, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(StudyClassType::class, $studyClass);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_study_class_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('study_class/edit.html.twig', [
            'study_class' => $studyClass,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_study_class_delete', methods: ['POST'])]
    public function delete(Request $request, StudyClass $studyClass, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$studyClass->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($studyClass);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_study_class_index', [], Response::HTTP_SEE_OTHER);
    }
}
