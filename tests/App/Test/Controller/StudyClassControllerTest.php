<?php

namespace App\Test\Controller;

use App\Entity\User;
use App\Entity\StudyClass;
use App\Entity\Student;
use App\Entity\Session;
use App\Entity\StudentClassRegistered;
use App\Entity\SessionStudyClassPresence;
use App\Entity\Payment;
use App\Entity\Room;
use App\Entity\Teacher;
use App\Entity\ParentEntity;
use App\Repository\SessionRepository;
use App\Service\StudyClassService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\HttpFoundation\Response;

final class StudyClassControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $em;
    private string $buildDir;

    protected function setUp(): void
    {
        parent::setUp();
        $this->client = static::createClient();
        $this->em = static::getContainer()->get('doctrine')->getManager();

        // Stub Webpack Encore (Twig)
        $this->buildDir = static::getContainer()->getParameter('kernel.project_dir') . '/public/build';
        if (!is_dir($this->buildDir)) {
            @mkdir($this->buildDir, 0777, true);
        }
        file_put_contents($this->buildDir . '/entrypoints.json', json_encode([
            'entrypoints' => [
                'app' => ['js' => ['/build/app.js'], 'css' => []],
            ],
        ], JSON_THROW_ON_ERROR));
        file_put_contents($this->buildDir . '/manifest.json', json_encode(new \stdClass(), JSON_THROW_ON_ERROR));

        // Auth: ROLE_TEACHER
        $user = (new User());
        // Adapte à tes setters exacts :
        $user->setEmail('teacher@test.dev');
        $user->setPassword('dummy');
        $user->setRoles(['ROLE_TEACHER']);
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

    /* ---------- Fabriques d’entités avec setters explicites ---------- */

    private function makeParent(string $first = 'Parent', string $last = 'Demo'): ParentEntity
    {
        $p = new ParentEntity();
        $p->setFatherFirstName($first);
        $p->setFatherLastName($last);
        $p->setMotherFirstName($first);
        $p->setMotherLastName($last);
        $p->setFamilyStatus('maried');
        $p->setFatherEmail(sprintf('parent+%s@test.dev', uniqid()));
        $p->setMotherEmail(sprintf('parent+%s@test.dev', uniqid()));
        $p->setFatherPhone('0612345678');
        $p->setMotherPhone('0612345678');

        $this->em->persist($p);
        $this->em->flush();
        return $p;
    }

    private function makeStudent(string $first = 'Ali', string $last = 'Test'): Student
    {
        $s = new Student();
        $s->setFirstName($first);
        $s->setLastName($last);
        $s->setLevel('CP');
        $s->setBirthDate(new \DateTimeImmutable('2012-05-01'));
        $s->setGender('M');
        $s->setAddress('1 rue du Test');
        $s->setCity('Grenoble');
        $s->setPostalCode('38000');

        // ⚠️ Choisis le bon setter selon ton entité:
        // Si c’est setParentEntity() au lieu de setParent(), remplace la ligne suivante.
        if (method_exists($s, 'setParent')) {
            $s->setParent($this->makeParent());
        } else {
            $s->setParent($this->makeParent());
        }

        $this->em->persist($s);
        $this->em->flush();
        return $s;
    }

    private function makeTeacher(string $fn = 'John', string $ln = 'Doe', ?string $email = null): Teacher
    {
        $t = new Teacher();
        $t->setFirstName($fn);
        $t->setLastName($ln);
        $t->setPhoneNumber('2012345678');
        $t->setEducationLevel('BAC+5');

        $t->setEmail($email ?? sprintf('teacher+%s@test.dev', uniqid()));

        $this->em->persist($t);
        $this->em->flush();
        return $t;
    }

    private function makeRoom(string $name = 'Salle 101'): Room
    {
        $r = new Room();
        $r->setName($name);
        $r->setMaxStudents(50);

        $this->em->persist($r);
        $this->em->flush();
        return $r;
    }

    private function makeStudyClass(
        string $name = 'Class A',
        string $level = 'CP',
        string $speciality = 'Math',
        string $day = StudyClass::DAY_SATURDAY,
        string $classType = StudyClass::CLASS_TYPE_AUTRE,
        ?string $schoolYear = StudyClass::SCHOOL_YEAR_2024_2025
    ): StudyClass {
        $sc = new StudyClass();
        $sc->setName($name);
        $sc->setLevel($level);
        $sc->setSpeciality($speciality);
        $sc->setDay($day);
        $sc->setStartHour(new \DateTimeImmutable('09:00'));
        $sc->setEndHour(new \DateTimeImmutable('10:30'));
        $sc->setClassType($classType);
        $sc->setSchoolYear($schoolYear);

        // 🔴 Important: lier explicitement une Room non-nulle
        $sc->setPrincipalRoom($this->makeRoom());
        // (Optionnel/selon mapping)
        $sc->setPrincipalTeacher($this->makeTeacher());

        $this->em->persist($sc);
        $this->em->flush();
        return $sc;
    }

    private function makeFutureSession(StudyClass $sc, string $when = '+2 days 10:00'): Session
    {
        $session = new Session();
        $session->setStudyClass($sc);
        $start = new \DateTimeImmutable($when);
        $session->setStartTime($start);
        $session->setEndTime($start->modify('+1 hour'));
        $room = $this->makeRoom();
        $teacher = $this->makeTeacher();
        $session->setTeacher($teacher);
        $session->setRoom($room);

        $this->em->persist($session);
        $this->em->flush();
        return $session;
    }

    private function registerStudent(StudyClass $sc, Student $st): StudentClassRegistered
    {
        $scr = new StudentClassRegistered($sc, $st);
        $scr->setActive(true);
        $this->em->persist($scr);
        $this->em->flush();
        return $scr;
    }

    private function makePayment(StudyClass $sc, ?Student $st = null): Payment
    {
        $p = new Payment();
        $parent = $this->makeParent();
        $p->setStudyClass($sc);
        $p->setParent($parent);
        $p->setStudent($st ?? $this->makeStudent());
        $p->setPaymentType('cash');
        $p->setServiceType('arabic');
        $p->setPaymentDate(new \DateTimeImmutable('now'));
        // Adapte selon ton entité: setAmount() ou setAmountCents()
        if (method_exists($p, 'setAmount')) {
            $p->setAmount(1000);
        } else {
            $p->setAmountPaid(1000);
        }

        if (method_exists($p, 'setPaidAt')) {
            $p->setPaidAt(new \DateTimeImmutable('now'));
        } elseif (method_exists($p, 'setCreatedAt')) {
            $p->setCreatedAt(new \DateTimeImmutable('now'));
        }

        $this->em->persist($p);
        $this->em->flush();
        return $p;
    }

    /* ----------------- TESTS ----------------- */

    public function testIndex_and_Search(): void
    {
        $this->makeStudyClass('Math CP');
        $this->makeStudyClass('Histoire CE2');

        // sans search
        $this->client->request('GET', '/study-class/?page=1');
        self::assertResponseIsSuccessful();

        // avec search
        $this->client->request('GET', '/study-class/?search=Math');
        self::assertResponseIsSuccessful();
    }

    public function testGetAllStudyClass_returnsJsonAndMap(): void
    {
        $sc = $this->makeStudyClass('Class JSON');
        $this->registerStudent($sc, $this->makeStudent());

        $this->client->request('GET', '/study-class/all-study-class');
        self::assertResponseIsSuccessful();
        self::assertResponseHeaderSame('content-type', 'application/json');

        $json = json_decode($this->client->getResponse()->getContent(), true);
        self::assertIsArray($json['studyClass']);
        self::assertIsArray($json['studentCountMap']);
        self::assertArrayHasKey($sc->getId(), $json['studentCountMap']);
    }

    public function testNewPage_and_Edit_and_Planning(): void
    {
        $this->client->request('GET', '/study-class/new');
        self::assertResponseIsSuccessful();

        $sc = $this->makeStudyClass('Class Edit');
        $this->client->request('GET', '/study-class/'.$sc->getId().'/edit');
        self::assertResponseIsSuccessful();

        $this->client->request('GET', '/study-class/planning');
        self::assertResponseIsSuccessful();
    }

    public function testShow(): void
    {
        $sc = $this->makeStudyClass('Class Show');
        $st = $this->makeStudent();
        $this->registerStudent($sc, $st);

        $this->client->request('GET', '/study-class/'.$sc->getId());
        self::assertResponseIsSuccessful();
    }

    public function testNewStudentToClass_createsPresenceForFutureSessions(): void
    {
        $sc = $this->makeStudyClass('Class Inscription');
        $st = $this->makeStudent();
        $futureSession = $this->makeFutureSession($sc, '+3 days 11:00');

        // mock repo pour garantir le retour de la session future
        $repo = $this->createMock(SessionRepository::class);
        $repo->method('findFutureSessions')->willReturn([$futureSession]);
        static::getContainer()->set(SessionRepository::class, $repo);

        $payload = json_encode(['studentIds' => [$st->getId()]], JSON_THROW_ON_ERROR);
        $this->client->request(
            'POST',
            '/study-class/new-student-to-class/'.$sc->getId(),
            server: ['CONTENT_TYPE' => 'application/json', 'HTTP_ACCEPT' => 'application/json'],
            content: $payload
        );
        self::assertResponseIsSuccessful();

        $countPresence = $this->em->getRepository(SessionStudyClassPresence::class)->count(['student' => $st]);
        self::assertSame(1, $countPresence);
    }

    public function testDeleteStudentFromClassAlsoRemovesFuturePresences(): void
    {
        // Arrange
        $sc  = $this->makeStudyClass('Class Delete Student');
        $st  = $this->makeStudent();
        $scr = $this->registerStudent($sc, $st);

        // S'assure que l'id de l'inscription est bien hydraté (Option A)
        $this->em->refresh($scr);
        $scrId = $scr->getId();
        self::assertNotEmpty($scrId, 'StudentClassRegistered id should not be null');

        // Crée une session future + une présence pour cet étudiant
        $future   = $this->makeFutureSession($sc, '+4 days 10:00');
        $presence = new SessionStudyClassPresence($st, $future);
        $this->em->persist($presence);
        $this->em->flush();

        // Mock du repo pour renvoyer la session future au contrôleur
        $repo = $this->createMock(SessionRepository::class);
        $repo->method('findFutureSessions')->willReturn([$future]);
        static::getContainer()->set(SessionRepository::class, $repo);

        // Act
        $this->client->request('POST', '/study-class/delete-student-from-class/' . $scrId);

        // Assert
        self::assertResponseIsSuccessful();

        // 1) L'inscription doit être supprimée (on peut utiliser l'id capturé)
        self::assertNull(
            $this->em->getRepository(StudentClassRegistered::class)->find($scrId),
            'StudentClassRegistered should be deleted'
        );

        // 2) La présence future doit être supprimée
        // >>> IMPORTANT : SessionStudyClassPresence a probablement une clé composite (pas d'id simple)
        // Donc on vérifie via findOneBy() sur (student, session) au lieu de find($presence->getId()).
        self::assertNull(
            $this->em->getRepository(SessionStudyClassPresence::class)->findOneBy([
                'student' => $st,
                'session' => $future,
            ]),
            'Future presence should be deleted'
        );

        // Variante équivalente possible :
        // self::assertSame(
        //     0,
        //     $this->em->getRepository(SessionStudyClassPresence::class)->count([
        //         'student' => $st,
        //         'session' => $future,
        //     ])
        // );
    }



    public function testDeactivateStudentFromClass(): void
    {
        $sc = $this->makeStudyClass('Class Deactivate');
        $st = $this->makeStudent();
        $scr = $this->registerStudent($sc, $st);

        $this->client->request('POST', '/study-class/deactivate-student-from-class/'.$scr->getId());
        self::assertResponseIsSuccessful();

        $fresh = $this->em->getRepository(StudentClassRegistered::class)->find($scr->getId());
        $isInactive = method_exists($fresh, 'isActive') ? !$fresh->isActive() : (method_exists($fresh, 'getActive') ? !$fresh->getActive() : true);
        self::assertTrue($isInactive);
    }

    public function testDeleteRefusesWhenPaymentsExist_and_DeleteWhenNoDependencies(): void
    {
        // refuse avec paiement -> contrôleur renvoie 400 (apiErrorResponse)
        $sc1 = $this->makeStudyClass('Class With Payment');
        $this->makePayment($sc1);

        $this->client->request('DELETE', '/study-class/'.$sc1->getId(), server: ['HTTP_ACCEPT' => 'application/json']);
        self::assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST); // 400 attendu
        $json = json_decode($this->client->getResponse()->getContent(), true);
        $msg = $json['message']['text'] ?? $json['error'] ?? '';
        self::assertStringContainsString('Impossible de supprimer', (string)$msg);
        self::assertNotNull($this->em->getRepository(StudyClass::class)->find($sc1->getId()));

        // supprime sans dépendances
        $sc2 = $this->makeStudyClass('Class No Payment');
        $id = $sc2->getId();
        $this->client->request('DELETE', '/study-class/'.$id, server: ['HTTP_ACCEPT' => 'application/json']);
        self::assertResponseIsSuccessful();
        self::assertNull($this->em->getRepository(StudyClass::class)->find($id));
    }

    public function testPlanningPage(): void
    {
        $this->client->request('GET', '/study-class/planning');
        self::assertResponseIsSuccessful();
    }

    public function testListFilteredReturnsJson(): void
    {
        $c1 = $this->makeStudyClass('Alpha');
        $c2 = $this->makeStudyClass('Beta');

        $this->client->request('GET', '/study-class/list');
        self::assertResponseIsSuccessful();
        self::assertResponseHeaderSame('content-type', 'application/json');

        $content = $this->client->getResponse()->getContent();
        $decoded = json_decode($content, true);
        self::assertIsArray($decoded);
        $ids = array_column($decoded, 'id');
        self::assertContains($c1->getId(), $ids);
        self::assertContains($c2->getId(), $ids);
    }
}
