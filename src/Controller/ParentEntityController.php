<?php

namespace App\Controller;

use App\Entity\ParentEntity;
use App\Form\ParentEntityType;
use App\Model\ApiResponseTrait;
use App\Repository\ParentsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\SerializerInterface;

#[IsGranted('ROLE_MANAGER')]
#[Route('/parent')]
class ParentEntityController extends AbstractController
{
    use ApiResponseTrait;
    #[Route('/', name: 'app_parent_entity_index', options: ['expose' => true], methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager, Request $request): Response
    {
        return $this->render('parent_entity/index.html.twig', [

        ]);
    }

    #[Route('/list', name: 'api_parent_list',  options: ['expose' => true], methods: ['GET'])]
    public function parentList(Request $request, SerializerInterface $serializer, ParentsRepository $parentsRepository): JsonResponse
    {
        $parents = $parentsRepository->findAll();

        return $this->apiResponse([
            'text'   => 'Remboursement créé avec succès.',
            'parents' => json_decode($serializer->serialize($parents, 'json', [
                'groups' => ['read_parent'],
                'enable_max_depth' => true,
                'circular_reference_handler' => fn($o) => method_exists($o,'getId') ? $o->getId() : spl_object_id($o),
            ]), true),
        ], Response::HTTP_CREATED);
    }

//    #[Route('/', name: 'app_parent_entity_index', options: ['expose' => true], methods: ['GET'])]
//    public function index(EntityManagerInterface $entityManager, Request $request): Response
//    {
//        $page = $request->query->getInt('page', 1); // Page actuelle, par défaut 1
//        $limit = 10; // Limite des résultats par page
//        $search = $request->query->get('search', ''); // Récupère le critère de recherche
//
//        $queryBuilder = $entityManager->getRepository(ParentEntity::class)
//            ->createQueryBuilder('p');
//
//        // Filtrage par nom, prénom, ou email
//        if (!empty($search)) {
//            $queryBuilder
//                ->where('p.fatherLastName LIKE :search')
//                ->orWhere('p.fatherFirstName LIKE :search')
//                ->orWhere('p.fatherEmail LIKE :search')
//                ->orWhere('p.motherLastName LIKE :search')
//                ->orWhere('p.motherFirstName LIKE :search')
//                ->orWhere('p.motherEmail LIKE :search')
//                ->setParameter('search', '%' . $search . '%');
//        }
//
//        $queryBuilder
//            ->setFirstResult(($page - 1) * $limit) // Offset
//            ->setMaxResults($limit); // Limite
//
//        $paginator = new Paginator($queryBuilder);
//
//        return $this->render('parent_entity/index.html.twig', [
//            'parent_entities' => $paginator,
//            'current_page' => $page,
//            'total_pages' => ceil(count($paginator) / $limit),
//            'search' => $search
//        ]);
//    }

    #[Route('/new', name: 'app_parent_entity_new', options: ['expose' => true], methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $parentEntity = new ParentEntity();
        $form = $this->createForm(ParentEntityType::class, $parentEntity);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($parentEntity);
            $entityManager->flush();

            return $this->redirectToRoute('app_parent_entity_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('parent_entity/new.html.twig', [
            'parent_entity' => $parentEntity,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_parent_entity_show', options: ['expose' => true], methods: ['GET'])]
    public function show(ParentEntity $parentEntity): Response
    {
        $students = $parentEntity->getStudents();
        return $this->render('parent_entity/show.html.twig', [
            'parentEntity' => $parentEntity,
            'students' => $students,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_parent_entity_edit', options: ['expose' => true], methods: ['GET', 'POST'])]
    public function edit(Request $request, ParentEntity $parentEntity, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ParentEntityType::class, $parentEntity);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_parent_entity_show', ['id' => $parentEntity->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('parent_entity/edit.html.twig', [
            'parent_entity' => $parentEntity,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_parent_entity_delete', options: ['expose' => true], methods: ['POST'])]
    public function delete(Request $request, ParentEntity $parentEntity, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$parentEntity->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($parentEntity);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_parent_entity_index', [], Response::HTTP_SEE_OTHER);
    }
}
