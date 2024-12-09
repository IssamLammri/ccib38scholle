<?php

namespace App\Controller;

use App\Entity\Room;
use App\Form\RoomType;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
#[Route('/room')]
class RoomController extends AbstractController
{
    #[Route('/', name: 'app_room_index', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager, Request $request): Response
    {
        $page = $request->query->getInt('page', 1); // Current page, default is 1
        $limit = 10; // Limit results per page
        $search = $request->query->get('search', ''); // Search filter

        $queryBuilder = $entityManager->getRepository(Room::class)
            ->createQueryBuilder('r');

        // Filter by room name or comment if search is present
        if (!empty($search)) {
            $queryBuilder
                ->where('r.name LIKE :search')
                ->orWhere('r.comment LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }

        $queryBuilder
            ->setFirstResult(($page - 1) * $limit) // Offset
            ->setMaxResults($limit); // Limit

        $paginator = new Paginator($queryBuilder);

        return $this->render('room/index.html.twig', [
            'rooms' => $paginator,
            'current_page' => $page,
            'total_pages' => ceil(count($paginator) / $limit),
            'search' => $search
        ]);
    }


    #[Route('/new', name: 'app_room_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $room = new Room();
        $form = $this->createForm(RoomType::class, $room);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($room);
            $entityManager->flush();

            return $this->redirectToRoute('app_room_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('room/new.html.twig', [
            'room' => $room,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_room_show', methods: ['GET'])]
    public function show(Room $room): Response
    {
        return $this->render('room/show.html.twig', [
            'room' => $room,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_room_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Room $room, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(RoomType::class, $room);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_room_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('room/edit.html.twig', [
            'room' => $room,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_room_delete', methods: ['POST'])]
    public function delete(Request $request, Room $room, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$room->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($room);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_room_index', [], Response::HTTP_SEE_OTHER);
    }
}
