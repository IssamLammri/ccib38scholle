<?php

namespace App\Controller;

use App\Entity\Student;
use App\Entity\StudyClass;
use App\Model\ApiResponseTrait;
use App\Repository\InvoiceRepository;
use App\Repository\PaymentRepository;
use App\Repository\SessionRepository;
use App\Repository\StudentClassRegisteredRepository;
use App\Repository\TeacherRepository;
use App\Service\MailService;
use App\Service\SmsService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\SerializerInterface;
use App\Repository\StudentRepository;

// WARNING: this controller is public
class CheckInController extends AbstractController
{
    use ApiResponseTrait;

    public function __construct(
        private EntityManagerInterface $entityManager,
    ){
    }
    
    #[Route('/check-in', name: 'app_check_in', methods: ['POST'])]
    public function register(
        Request $request,
        StudentRepository $studentRepository,
        StudentClassRegisteredRepository $studentClassRegisteredRepository
    ): Response {
        // Read "code" from the POST JSON body
        $data = json_decode($request->getContent(), true);

        if ($data === null) {
        return $this->json([
            'error' => 'Invalid JSON or missing Content-Type: application/json',
            'raw'   => $request->getContent()
        ], 400);
    }

        $code = $data['code'] ?? null;

        if (!$code) {
            return $this->json(['error' => 'Missing "code" in the request body.'], 400);
        }

        // Ensure it's a string of digits
        if (!preg_match('/^\d+$/', $code)) {
            return $this->json(['error' => 'The code must contain digits only.'], 400);
        }

        // Ensure it's exactly 9 digits long
        if (strlen($code) !== 9) {
            return $this->json(['error' => 'The code must be exactly 9 digits long.'], 400);
        }

        // Ensure first digit is 1 or 2
        if ($code[0] !== '1' && $code[0] !== '2') {
            return $this->json(['error' => 'The first digit must be 1 or 2.'], 400);
        }

        $studentId = intval(substr($code, 1));

        if ($studentId === 0) {
            return $this->json(['error' => 'Invalid student ID extracted from code.'], 400);
        }

        $student = $studentRepository->find($studentId);

        if (!$student) {
            return $this->json(['error' => 'Student not found.'], 404);
        }

        $registration = $studentClassRegisteredRepository->findOneBy([
            'student' => $student
        ]);

        if (!$registration) {
            return $this->json(['error' => 'This student is not registered in any class.'], 404);
        }

        $studyClass = $registration->getStudyClass();

        if (!$studyClass) {
            return $this->json(['error' => 'Class data not found for this student.'], 404);
        }

        return $this->json([
            'id'        => (int) $student->getId(),
            'level'     => $studyClass->getLevel(),
            'speciality'=> $studyClass->getSpeciality(),
            'firstName' => $student->getFirstName(),
            'lastName'  => $student->getLastName(),
            'start'     => $studyClass->getStartHour()->format('H:i'),
            'end'       => $studyClass->getEndHour()->format('H:i'),
        ]);
    }
}
