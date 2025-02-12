<?php

namespace App\Controller;

use App\Entity\Student;
use App\Form\StudentType;
use App\Repository\StudentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_MANAGER')]
#[Route('/student')]
class StudentController extends AbstractController
{
    public function __construct(
        private StudentRepository $studentRepository,
    ){
    }

    #[Route('/all-students', name: 'all_students', options: ['expose' => true], methods: ['GET'])]
    public function getAllStudents(): JsonResponse
    {
        $allStudents = $this->studentRepository->findAll();
        return $this->json([
            'allStudents' => $allStudents,
        ], 200, [], ['groups' => 'read_student']);
    }

    #[Route('/', name: 'app_student_index',  options: ['expose' => true], methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager, Request $request): Response
    {
        $page = $request->query->getInt('page', 1); // Current page, default is 1
        $limit = 10; // Limit results per page
        $search = $request->query->get('search', ''); // Search filter

        $queryBuilder = $entityManager->getRepository(Student::class)
            ->createQueryBuilder('s');

        // Filter by last name, first name, or email if search is present
        if (!empty($search)) {
            $queryBuilder
                ->where('s.lastName LIKE :search')
                ->orWhere('s.firstName LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }

        $queryBuilder
            ->setFirstResult(($page - 1) * $limit) // Offset
            ->setMaxResults($limit); // Limit

        $paginator = new Paginator($queryBuilder);

        return $this->render('student/index.html.twig', [
            'students' => $paginator,
            'current_page' => $page,
            'total_pages' => ceil(count($paginator) / $limit),
            'search' => $search
        ]);
    }


    #[Route('/new', name: 'app_student_new',  options: ['expose' => true], methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $student = new Student();
        $form = $this->createForm(StudentType::class, $student);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($student);
            $entityManager->flush();

            return $this->redirectToRoute('app_student_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('student/new.html.twig', [
            'student' => $student,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_student_show', methods: ['GET'])]
    public function show(Student $student): Response
    {
        return $this->render('student/show.html.twig', [
            'student' => $student,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_student_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Student $student, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(StudentType::class, $student);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_student_show', ['id' => $student->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('student/edit.html.twig', [
            'student' => $student,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_student_delete', methods: ['POST'])]
    public function delete(Request $request, Student $student, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$student->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($student);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_student_index', [], Response::HTTP_SEE_OTHER);
    }
}
