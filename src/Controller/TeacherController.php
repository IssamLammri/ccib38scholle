<?php

namespace App\Controller;

use App\Entity\Teacher;
use App\Entity\User;
use App\Form\TeacherType;
use App\Repository\StudyClassRepository;
use App\Repository\TeacherRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_MANAGER')]
#[Route('/teacher')]
class TeacherController extends AbstractController
{
    #[Route('/', name: 'app_teacher_index', options: ['expose' => true], methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager, Request $request): Response
    {
        $page = $request->query->getInt('page', 1); // Page actuelle, par défaut 1
        $limit = 10; // Limite des résultats par page
        $search = $request->query->get('search', ''); // Filtre de recherche

        $queryBuilder = $entityManager->getRepository(Teacher::class)
            ->createQueryBuilder('t');

        // Filtrer par lastName ou firstName si la recherche est présente
        if (!empty($search)) {
            $queryBuilder
                ->where('t.lastName LIKE :search')
                ->orWhere('t.firstName LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }

        $queryBuilder
            ->setFirstResult(($page - 1) * $limit) // Offset
            ->setMaxResults($limit); // Limite

        $paginator = new Paginator($queryBuilder);

        return $this->render('teacher/index.html.twig', [
            'teachers' => $paginator,
            'current_page' => $page,
            'total_pages' => ceil(count($paginator) / $limit),
            'search' => $search
        ]);
    }

    #[Route('/all-teachers', name: 'all_teachers', options: ['expose' => true], methods: ['GET'])]
    public function getAllTeachers(
        TeacherRepository $teacherRepository,
        CsrfTokenManagerInterface $csrfTokenManager
    ): JsonResponse
    {
        $teachers = $teacherRepository->findAll();

        $allTeachers = [];

        foreach ($teachers as $teacher) {

            // ✅ mapping des classes (relation) -> tableau simple
            $classes = [];
            foreach ($teacher->getClasses() as $cls) {
                $classes[] = [
                    'id' => $cls->getId(),
                    'name' => $cls->getName(),         // c’est ce que tu utilises dans Vue (cls.name)
                    'level' => $cls->getLevel(),       // optionnel
                    'speciality' => $cls->getSpeciality(), // optionnel
                ];
            }

            $allTeachers[] = [
                'id' => $teacher->getId(),
                'firstName' => $teacher->getFirstName(),
                'lastName' => $teacher->getLastName(),
                'email' => $teacher->getEmail(),
                'phoneNumber' => $teacher->getPhoneNumber(),
                'educationLevel' => $teacher->getEducationLevel(),
                'specialities' => $teacher->getSpecialities(),

                // ✅ relation préparée pour Vue
                'classes' => $classes,

                // ✅ TOKEN par ID (IMPORTANT)
                'deleteToken' => $csrfTokenManager->getToken('delete' . $teacher->getId())->getValue(),
            ];
        }

        return new JsonResponse([
            'allTeachers' => $allTeachers,
        ], 200);
    }

    #[Route('/new', name: 'app_teacher_new', options: ['expose' => true], methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $teacher = new Teacher();
        $form = $this->createForm(TeacherType::class, $teacher);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $userTeacher = (new User())
                ->setEmail($teacher->getEmail())
                ->setFirstName($teacher->getFirstName())
                ->setLastName($teacher->getLastName())
                ->setRoles(['ROLE_TEACHER'])
                ->setPassword('password');
            $teacher->setUser($userTeacher);
            $entityManager->persist($teacher);
            $entityManager->persist($userTeacher);
            $entityManager->flush();

            return $this->redirectToRoute('app_teacher_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('teacher/new.html.twig', [
            'teacher' => $teacher,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_teacher_show', options: ['expose' => true], methods: ['GET'])]
    public function show(Teacher $teacher): Response
    {
        return $this->render('teacher/show.html.twig', [
            'teacher' => $teacher,
        ]);
    }

    #[Route('/api/{id}', name: 'api_teacher_show', options: ['expose' => true], methods: ['GET'])]
    public function apiShow(Teacher $teacher): JsonResponse
    {
        // classes => format attendu par Vue: [{id,name,...}]
        $classes = [];
        foreach ($teacher->getClasses() as $cls) {
            $classes[] = [
                'id' => $cls->getId(),
                'name' => $cls->getName(),
                'level' => $cls->getLevel(),
                'speciality' => $cls->getSpeciality(),
                'day' => $cls->getDay(),
            ];
        }

        return new JsonResponse([
            'success' => true,
            'teacher' => [
                'id' => $teacher->getId(),
                'firstName' => $teacher->getFirstName(),
                'lastName' => $teacher->getLastName(),
                'email' => $teacher->getEmail(),
                'phoneNumber' => $teacher->getPhoneNumber(),
                'educationLevel' => $teacher->getEducationLevel(),
                'specialities' => $teacher->getSpecialities(),
                'classes' => $classes,
            ]
        ], 200);
    }

    #[Route('/api', name: 'api_teacher_create', options: ['expose' => true], methods: ['POST'])]
    public function apiCreate(
        Request $request,
        EntityManagerInterface $entityManager,
        StudyClassRepository $studyClassRepository
    ): JsonResponse {
        $data = json_decode($request->getContent(), true) ?? [];

        // ✅ validations simples (tu peux raffiner)
        $required = ['firstName','lastName','email','phoneNumber','educationLevel'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                return new JsonResponse(['success' => false, 'message' => "Champ requis: $field"], 422);
            }
        }

        $teacher = new Teacher();
        $teacher->setFirstName($data['firstName']);
        $teacher->setLastName($data['lastName']);
        $teacher->setEmail($data['email']);
        $teacher->setPhoneNumber($data['phoneNumber']);
        $teacher->setEducationLevel($data['educationLevel']);
        $teacher->setSpecialities($data['specialities'] ?? []);

        // ✅ classes (principalTeacher côté StudyClass)
        $classIds = $data['classIds'] ?? [];
        if (is_array($classIds) && count($classIds) > 0) {
            $classes = $studyClassRepository->findBy(['id' => $classIds]);
            foreach ($classes as $cls) {
                $cls->setPrincipalTeacher($teacher); // owning side
            }
        }

        // ✅ créer User (attention mot de passe en clair dans ton code actuel)
        $userTeacher = (new User())
            ->setEmail($teacher->getEmail())
            ->setFirstName($teacher->getFirstName())
            ->setLastName($teacher->getLastName())
            ->setRoles(['ROLE_TEACHER'])
            ->setPassword('password'); // ⚠️ à encoder plus tard

        $teacher->setUser($userTeacher);

        $entityManager->persist($userTeacher);
        $entityManager->persist($teacher);
        $entityManager->flush();

        return new JsonResponse(['success' => true, 'id' => $teacher->getId()], 201);
    }

    #[Route('/api/{id}', name: 'api_teacher_update', options: ['expose' => true], methods: ['PUT','POST'])]
    public function apiUpdate(
        Request $request,
        Teacher $teacher,
        EntityManagerInterface $entityManager,
        StudyClassRepository $studyClassRepository
    ): JsonResponse {
        $data = json_decode($request->getContent(), true) ?? [];

        $teacher->setFirstName($data['firstName'] ?? $teacher->getFirstName());
        $teacher->setLastName($data['lastName'] ?? $teacher->getLastName());
        $teacher->setEmail($data['email'] ?? $teacher->getEmail());
        $teacher->setPhoneNumber($data['phoneNumber'] ?? $teacher->getPhoneNumber());
        $teacher->setEducationLevel($data['educationLevel'] ?? $teacher->getEducationLevel());
        $teacher->setSpecialities($data['specialities'] ?? $teacher->getSpecialities());

        // ✅ mise à jour des classes :
        // 1) enlever l'ancien principalTeacher = teacher
        foreach ($teacher->getClasses() as $oldCls) {
            $oldCls->setPrincipalTeacher(null);
        }

        // 2) assigner les nouvelles
        $classIds = $data['classIds'] ?? [];
        if (is_array($classIds) && count($classIds) > 0) {
            $newClasses = $studyClassRepository->findBy(['id' => $classIds]);
            foreach ($newClasses as $cls) {
                $cls->setPrincipalTeacher($teacher);
            }
        }

        $entityManager->flush();

        return new JsonResponse(['success' => true], 200);
    }


    #[Route('/{id}/edit', name: 'app_teacher_edit', options: ['expose' => true], methods: ['GET', 'POST'])]
    public function edit(Request $request, Teacher $teacher, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(TeacherType::class, $teacher);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_teacher_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('teacher/edit.html.twig', [
            'teacher' => $teacher,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_teacher_delete', options: ['expose' => true], methods: ['POST'])]
    public function delete(Request $request, Teacher $teacher, EntityManagerInterface $entityManager): JsonResponse
    {
        // ✅ Si Axios envoie JSON, POST params est vide => on parse le body
        $data = json_decode($request->getContent(), true) ?? [];

        // fallback si un jour tu envoies un form
        $token = $data['_token'] ?? $request->request->get('_token');

        if (!$this->isCsrfTokenValid('delete'.$teacher->getId(), $token)) {
            return new JsonResponse(['success' => false, 'message' => 'CSRF invalide'], 403);
        }

        $entityManager->remove($teacher);
        $entityManager->flush();

        return new JsonResponse(['success' => true], 200);
    }
}
