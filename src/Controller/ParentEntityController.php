<?php

namespace App\Controller;

use App\Entity\ParentAmountDueHistory;
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

    #[Route('/{id}/edit', name: 'app_parent_entity_edit', options: ['expose' => true], methods: ['GET'])]
    public function edit(
        ParentEntity $parentEntity,
        SerializerInterface $serializer
    ): Response {
        $students = $parentEntity->getStudents();

        $parentJson = json_decode($serializer->serialize($parentEntity, 'json', [
            'groups' => ['read_parent'],
            'enable_max_depth' => true,
            'circular_reference_handler' => fn($o) => method_exists($o, 'getId') ? $o->getId() : spl_object_id($o),
        ]), true);

        $studentsJson = json_decode($serializer->serialize($students, 'json', [
            'groups' => ['read_parent'],
            'enable_max_depth' => true,
            'circular_reference_handler' => fn($o) => method_exists($o, 'getId') ? $o->getId() : spl_object_id($o),
        ]), true);

        return $this->render('parent_entity/edit.html.twig', [
            'parent_entity' => $parentJson,
            'students' => $studentsJson,
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

    #[Route('/{id}/api', name: 'api_parent_update', options: ['expose' => true], methods: ['PATCH'])]
    public function apiUpdate(
        Request $request,
        ParentEntity $parentEntity,
        EntityManagerInterface $em
    ): JsonResponse {
        $csrf = $request->headers->get('X-CSRF-TOKEN', '');
        if (!$this->isCsrfTokenValid('edit_parent_'.$parentEntity->getId(), $csrf)) {
            return $this->apiResponse(['message' => 'CSRF invalide'], Response::HTTP_FORBIDDEN);
        }

        $data = json_decode($request->getContent(), true);
        if (!is_array($data)) {
            return $this->apiResponse(['message' => 'JSON invalide'], Response::HTTP_BAD_REQUEST);
        }

        // --------------------
        // Mapping standard
        // --------------------
        $parentEntity->setFatherLastName($data['fatherLastName'] ?? 'Vide');
        $parentEntity->setFatherFirstName($data['fatherFirstName'] ?? 'Vide');
        $parentEntity->setFatherEmail($data['fatherEmail'] ?? 'Vide');
        $parentEntity->setFatherPhone($data['fatherPhone'] ?? 'Vide');

        $parentEntity->setMotherLastName($data['motherLastName'] ?? 'Vide');
        $parentEntity->setMotherFirstName($data['motherFirstName'] ?? 'Vide');
        $parentEntity->setMotherEmail($data['motherEmail'] ?? 'Vide');
        $parentEntity->setMotherPhone($data['motherPhone'] ?? 'Vide');

        $parentEntity->setFamilyStatus($data['familyStatus'] ?? '');

        // --------------------
        // Sécuriser la modif des montants : uniquement si la clé est envoyée
        // + historique si changement
        // --------------------

        $wantsToChangeAmounts =
            array_key_exists('amountDueArabic', $data) ||
            array_key_exists('amountDueSoutien', $data);

        // amountDueArabic
        if (array_key_exists('amountDueArabic', $data)) {
            $old = $parentEntity->getAmountDueArabic();
            $new = max(0, (int) $data['amountDueArabic']);

            if ($old !== $new) {
                $comment = trim((string)($data['amountDueArabicComment'] ?? ''));
                if ($comment === '') {
                    return $this->apiResponse([
                        'message' => "Commentaire obligatoire pour modifier le montant dû (Arabe).",
                    ], Response::HTTP_BAD_REQUEST);
                }

                $h = new ParentAmountDueHistory();
                $h->setParent($parentEntity);
                $h->setField('amountDueArabic');
                $h->setOldValue($old);
                $h->setNewValue($new);
                $h->setComment($comment);
                $h->setChangedBy($this->getUser()); // null si pas de sécurité / user anonyme

                $em->persist($h);

                $parentEntity->setAmountDueArabic($new);
            }
        }

        // amountDueSoutien
        if (array_key_exists('amountDueSoutien', $data)) {
            $old = $parentEntity->getAmountDueSoutien();
            $new = max(0, (int) $data['amountDueSoutien']);

            if ($old !== $new) {
                $comment = trim((string)($data['amountDueSoutienComment'] ?? ''));
                if ($comment === '') {
                    return $this->apiResponse([
                        'message' => "Commentaire obligatoire pour modifier le montant dû (Soutien).",
                    ], Response::HTTP_BAD_REQUEST);
                }

                $h = new ParentAmountDueHistory();
                $h->setParent($parentEntity);
                $h->setField('amountDueSoutien');
                $h->setOldValue($old);
                $h->setNewValue($new);
                $h->setComment($comment);
                $h->setChangedBy($this->getUser());

                $em->persist($h);

                $parentEntity->setAmountDueSoutien($new);
            }
        }

        $em->flush();

        return $this->apiResponse([
            'text' => 'Parent mis à jour avec succès.',
        ], Response::HTTP_OK);
    }
}
