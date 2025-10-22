<?php

namespace App\Test\Controller;

use App\Entity\User; // adapte si besoin
use App\Entity\StudyClass;
use App\Entity\Student;
use App\Entity\Session;
use App\Entity\StudentClassRegistered;
use App\Entity\SessionStudyClassPresence;
use App\Entity\Payment;
use App\Entity\Room;
use App\Entity\Teacher;
use App\Service\StudyClassService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;

final class StudyClassControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $em;

    protected function setUp(): void
    {
        parent::setUp();
        $this->client = static::createClient();
        $this->em = static::getContainer()->get('doctrine')->getManager();

        // --- Auth: user ROLE_TEACHER
        $user = new User();
        // Adapte pour ton entité user :
        $this->setIf($user, ['setEmail','setMail','setEmailAddress'], 'teacher@test.dev');
        $this->setIf($user, ['setPassword'], 'dummy');
        $this->setIf($user, ['setRoles'], ['ROLE_ADMIN']);
        $this->em->persist($user);
        $this->em->flush();

        $this->client->loginUser($user, 'main');
    }

    protected function tearDown(): void
    {
        if ($this->em && $this->em->isOpen()) {
            $this->em->clear();
            $this->em->getConnection()->close();
        }
        parent::tearDown();
    }

    // ---------------------------------
    // Helpers génériques "safe setters"
    // ---------------------------------
    private function setIf(object $o, array $candidateSetters, mixed $value): bool
    {
        foreach ($candidateSetters as $m) {
            if (method_exists($o, $m)) {
                $o->{$m}($value);
                return true;
            }
        }
        return false;
    }

    private function setDateIf(object $o, array $candidateSetters, \DateTimeInterface $value): bool
    {
        foreach ($candidateSetters as $m) {
            if (method_exists($o, $m)) {
                $o->{$m}($value);
                return true;
            }
        }
        return false;
    }

    private function makeStudyClass(
        string $name = 'Class A',
        string $level = 'CP',
        string $speciality = 'Math',
        string $day = StudyClass::DAY_SATURDAY,
        string $classType = StudyClass::CLASS_TYPE_AUTRE,
        ?string $schoolYear = StudyClass::SCHOOL_YEAR_2024_2025
    ): StudyClass {
        $sc = (new StudyClass())
            ->setName($name)
            ->setLevel($level)
            ->setSpeciality($speciality)
            ->setDay($day)
            ->setStartHour(new \DateTimeImmutable('09:00'))
            ->setEndHour(new \DateTimeImmutable('10:30'))
            ->setClassType($classType)
            ->setSchoolYear($schoolYear);

        $this->em->persist($sc);
        $this->em->flush();

        return $sc;
    }

    private function makeTeacher(string $fn = 'John', string $ln = 'Doe', string $email = null): Teacher
    {
        $t = new Teacher();
        $this->setIf($t, ['setFirstName','setPrenom'], $fn);
        $this->setIf($t, ['setLastName','setNom'], $ln);
        // email NOT NULL dans ta DB :
        $this->setIf($t, ['setEmail','setMail','setEmailAddress'], $email ?? sprintf('teacher+%s@test.dev', uniqid()));
        $this->em->persist($t);
        $this->em->flush();
        return $t;
    }

    private function makeRoom(string $name = 'Salle 101'): Room
    {
        $r = new Room();
        $this->setIf($r, ['setName','setLabel'], $name);
        $this->em->persist($r);
        $this->em->flush();
        return $r;
    }

    private function makeStudent(string $first = 'Ali', string $last = 'Test'): Student
    {
        $s = new Student();
        $this->setIf($s, ['setFirstName','setPrenom'], $first);
        $this->setIf($s, ['setLastName','setNom'], $last);

        // birth_date NOT NULL dans ta DB :
        $this->setDateIf($s, ['setBirthDate','setBirthdate','setDob','setDateNaissance'], new \DateTimeImmutable('2012-05-01'));

        // d’autres champs obligatoires éventuels (si existants) :
        $this->setIf($s, ['setEmail','setMail'], sprintf('student+%s@test.dev', uniqid()));
        $this->setIf($s, ['setGender','setSexe'], 'M');
        $this->setIf($s, ['setAddress','setAdresse'], '1 rue Test');
        $this->setIf($s, ['setCity','setVille'], 'Paris');

        $this->em->persist($s);
        $this->em->flush();

        return $s;
    }

    private function makeFutureSession(StudyClass $sc, string $when = '+2 days 10:00'): Session
    {
        $session = new Session();
        // Adapte les setters réels :
        $this->setIf($session, ['setStudyClass','setClasse'], $sc);
        $this->setDateIf($session, ['setStartAt','setStart','setDateStart','setDebut'], new \DateTimeImmutable($when));
        $this->setDateIf($session, ['setEndAt','setEnd','setDateEnd','setFin'], (new \DateTimeImmutable($when))->modify('+1 hour'));

        $this->em->persist($session);
        $this->em->flush();

        return $session;
    }

    private function registerStudent(StudyClass $sc, Student $st): StudentClassRegistered
    {
        $scr = new StudentClassRegistered($sc, $st); // d’après ton code constructeur
        $this->setIf($scr, ['setActive','setIsActive'], true);
        $this->em->persist($scr);
        $this->em->flush();

        return $scr;
    }

    private function makePayment(StudyClass $sc, ?Student $st = null): Payment
    {
        $p = new Payment();

        // Lier la classe
        $this->setIf($p, ['setStudyClass','setClasse'], $sc);

        // Essayer les noms de montant courants
        $this->setIf($p, ['setAmount','setMontant','setTotal','setPrice','setAmountCents'], 1000);
        // Statut / date
        $this->setDateIf($p, ['setPaidAt','setDatePaiement','setCreatedAt'], new \DateTimeImmutable('now'));
        $this->setIf($p, ['setStatus','setStatut'], 'paid');

        // Lier un étudiant si nécessaire
        $st ??= $this->makeStudent();
        $this->setIf($p, ['setStudent','setEleve','setPayer'], $st);

        $this->em->persist($p);
        $this->em->flush();

        return $p;
    }

    // ---------------------
    //        TESTS
    // ---------------------

    public function testIndex(): void
    {
        $this->makeStudyClass();
        $this->client->request('GET', '/study-class/');
        self::assertResponseIsSuccessful();
    }

    public function testGetAllStudyClass(): void
    {
        $sc = $this->makeStudyClass('Class JSON');

        $this->client->request('GET', '/study-class/all-study-class');
        self::assertResponseIsSuccessful();
        self::assertResponseHeaderSame('content-type', 'application/json');

        $json = json_decode($this->client->getResponse()->getContent(), true);
        self::assertIsArray($json['studyClass']);
        $ids = array_column($json['studyClass'], 'id');
        self::assertContains($sc->getId(), $ids);
    }

    public function testNewPage(): void
    {
        // Besoin d’au moins un prof et une salle
        //$this->makeTeacher();
        //$this->makeRoom();

        $this->client->request('GET', '/study-class/new');
        self::assertResponseIsSuccessful();
    }
//
//    public function testShow(): void
//    {
//        $sc = $this->makeStudyClass('Class Show');
//        $st = $this->makeStudent();
//        $this->registerStudent($sc, $st);
//
//        $this->client->request('GET', '/study-class/'.$sc->getId());
//        self::assertResponseIsSuccessful();
//    }
//
//    public function testNewStudentToClass(): void
//    {
//        $sc = $this->makeStudyClass('Class Inscription');
//        $st = $this->makeStudent();
//        $this->makeFutureSession($sc, '+3 days 11:00');
//
//        $payload = json_encode(['studentIds' => [$st->getId()]], JSON_THROW_ON_ERROR);
//        $this->client->request(
//            'POST',
//            '/study-class/new-student-to-class/'.$sc->getId(),
//            server: ['CONTENT_TYPE' => 'application/json'],
//            content: $payload
//        );
//        self::assertResponseIsSuccessful();
//
//        $repoSCR = $this->em->getRepository(StudentClassRegistered::class);
//        $repoPresence = $this->em->getRepository(SessionStudyClassPresence::class);
//
//        self::assertNotNull($repoSCR->findOneBy(['studyClass' => $sc, 'student' => $st]));
//        self::assertGreaterThan(0, $repoPresence->count(['student' => $st]));
//    }
//
//    public function testDeleteStudentFromClassAlsoRemovesFuturePresences(): void
//    {
//        $sc = $this->makeStudyClass('Class Delete Student');
//        $st = $this->makeStudent();
//        $scr = $this->registerStudent($sc, $st);
//
//        $future = $this->makeFutureSession($sc, '+4 days 10:00');
//        $presence = new SessionStudyClassPresence($st, $future);
//        $this->em->persist($presence);
//        $this->em->flush();
//
//        $this->client->request('POST', '/study-class/delete-student-from-class/'.$scr->getId());
//        self::assertResponseIsSuccessful();
//
//        $repoSCR = $this->em->getRepository(StudentClassRegistered::class);
//        $repoPresence = $this->em->getRepository(SessionStudyClassPresence::class);
//
//        self::assertNull($repoSCR->find($scr->getId()));
//        self::assertNull($repoPresence->find($presence->getId()));
//    }
//
//    public function testDeactivateStudentFromClass(): void
//    {
//        $sc = $this->makeStudyClass('Class Deactivate');
//        $st = $this->makeStudent();
//        $scr = $this->registerStudent($sc, $st);
//
//        $this->client->request('POST', '/study-class/deactivate-student-from-class/'.$scr->getId());
//        self::assertResponseIsSuccessful();
//
//        // re-fetch depuis DB pour refléter la vraie valeur
//        $fresh = $this->em->getRepository(StudentClassRegistered::class)->find($scr->getId());
//        $isInactive = method_exists($fresh, 'isActive') ? !$fresh->isActive() : (method_exists($fresh, 'getActive') ? !$fresh->getActive() : true);
//        self::assertTrue($isInactive, 'StudentClassRegistered becomes inactive');
//    }
//
//    public function testEditPage(): void
//    {
//        $sc = $this->makeStudyClass('Class Edit');
//        $this->client->request('GET', '/study-class/'.$sc->getId().'/edit');
//        self::assertResponseIsSuccessful();
//    }
//
//    public function testDeleteRefusesWhenPaymentsExist(): void
//    {
//        $sc = $this->makeStudyClass('Class With Payment');
//        $this->makePayment($sc);
//
//        $this->client->request('DELETE', '/study-class/'.$sc->getId(), server: ['HTTP_ACCEPT' => 'application/json']);
//        self::assertResponseStatusCodeSame(200); // ApiResponseTrait renvoie 200 + message d’erreur
//
//        $json = json_decode($this->client->getResponse()->getContent(), true);
//        $msg = $json['message'] ?? $json['error'] ?? $json['detail'] ?? '';
//        self::assertStringContainsString('Impossible de supprimer', (string)$msg);
//
//        self::assertNotNull($this->em->getRepository(StudyClass::class)->find($sc->getId()));
//    }
//
//    public function testDeleteWhenNoDependencies(): void
//    {
//        $sc = $this->makeStudyClass('Class No Payment');
//        $id = $sc->getId();
//
//        $this->client->request('DELETE', '/study-class/'.$id, server: ['HTTP_ACCEPT' => 'application/json']);
//        self::assertResponseIsSuccessful();
//
//        self::assertNull($this->em->getRepository(StudyClass::class)->find($id));
//    }
//
//    public function testPlanningPage(): void
//    {
//        $this->client->request('GET', '/study-class/planning');
//        self::assertResponseIsSuccessful();
//    }
//
//    public function testListFilteredReturnsJson(): void
//    {
//        $c1 = $this->makeStudyClass('Alpha');
//        $c2 = $this->makeStudyClass('Beta');
//
//        $this->client->request('GET', '/study-class/list');
//        self::assertResponseIsSuccessful();
//        self::assertResponseHeaderSame('content-type', 'application/json');
//
//        // Ton action retourne du JSON **déjà encodé** (string) dans un Response non-JsonResponse
//        $content = $this->client->getResponse()->getContent();
//        $decoded = json_decode($content, true);
//        self::assertIsArray($decoded);
//        $ids = array_column($decoded, 'id');
//        self::assertContains($c1->getId(), $ids);
//        self::assertContains($c2->getId(), $ids);
//    }
//
//    public function testSaveDataUsesService(): void
//    {
//        $sc = $this->makeStudyClass('Update me');
//
//        // Mock service: ?array (null = OK, array = erreurs)
//        $mock = $this->createMock(StudyClassService::class);
//        $mock->expects(self::once())
//            ->method('updateFromArray')
//            ->with($sc, self::callback(fn($arr) => isset($arr['name']) && $arr['name'] === 'Updated'))
//            ->willReturn(null); // succès
//
//        static::getContainer()->set(StudyClassService::class, $mock);
//
//        $payload = json_encode(['name' => 'Updated'], JSON_THROW_ON_ERROR);
//        $this->client->request(
//            'POST',
//            '/study-class/save-data/'.$sc->getId(),
//            server: ['CONTENT_TYPE' => 'application/json', 'HTTP_ACCEPT' => 'application/json'],
//            content: $payload
//        );
//
//        self::assertResponseIsSuccessful();
//    }
//
//    public function testSaveDataReturnsValidationErrorFromService(): void
//    {
//        $sc = $this->makeStudyClass('Update me (bad)');
//
//        $mock = $this->createMock(StudyClassService::class);
//        $mock->expects(self::once())
//            ->method('updateFromArray')
//            ->willReturn(['Erreur de validation']); // <-- conforme au type ?array
//
//        static::getContainer()->set(StudyClassService::class, $mock);
//
//        $payload = json_encode(['name' => 'Bad'], JSON_THROW_ON_ERROR);
//
//        $this->client->request(
//            'POST',
//            '/study-class/save-data/'.$sc->getId(),
//            server: ['CONTENT_TYPE' => 'application/json', 'HTTP_ACCEPT' => 'application/json'],
//            content: $payload
//        );
//
//        self::assertResponseStatusCodeSame(400);
//    }
//
//    public function testSaveDataCreateUsesService(): void
//    {
//        $mock = $this->createMock(StudyClassService::class);
//        $mock->expects(self::once())
//            ->method('updateFromArray')
//            ->willReturn(null);
//
//        static::getContainer()->set(StudyClassService::class, $mock);
//
//        $payload = json_encode([
//            'name'        => 'Nouvelle Classe',
//            'level'       => 'CE1',
//            'speciality'  => 'Français',
//            'day'         => StudyClass::DAY_SUNDAY,
//            'classType'   => StudyClass::CLASS_TYPE_ARABE,
//            'startHour'   => '09:00',
//            'endHour'     => '10:00',
//            'schoolYear'  => StudyClass::SCHOOL_YEAR_2024_2025,
//        ], JSON_THROW_ON_ERROR);
//
//        $this->client->request(
//            'POST',
//            '/study-class/save-data-create/',
//            server: ['CONTENT_TYPE' => 'application/json', 'HTTP_ACCEPT' => 'application/json'],
//            content: $payload
//        );
//
//        self::assertResponseStatusCodeSame(201);
//        $json = json_decode($this->client->getResponse()->getContent(), true);
//        self::assertArrayHasKey('id', $json);
//        self::assertGreaterThan(0, (int) $json['id']);
//    }
//
//    public function testSaveDataCreateReturnsErrorFromService(): void
//    {
//        $mock = $this->createMock(StudyClassService::class);
//        $mock->expects(self::once())
//            ->method('updateFromArray')
//            ->willReturn(['Erreur de validation']); // <-- conforme au type ?array
//
//        static::getContainer()->set(StudyClassService::class, $mock);
//
//        $payload = json_encode(['name' => 'Bad Create'], JSON_THROW_ON_ERROR);
//
//        $this->client->request(
//            'POST',
//            '/study-class/save-data-create/',
//            server: ['CONTENT_TYPE' => 'application/json', 'HTTP_ACCEPT' => 'application/json'],
//            content: $payload
//        );
//
//        self::assertResponseStatusCodeSame(400);
//    }
}
