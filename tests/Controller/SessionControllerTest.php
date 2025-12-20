<?php
//
//namespace App\Test\Controller;
//
//use App\Entity\Session;
//use Doctrine\ORM\EntityManagerInterface;
//use Doctrine\ORM\EntityRepository;
//use Symfony\Bundle\FrameworkBundle\KernelBrowser;
//use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
//
//class SessionControllerTest extends WebTestCase
//{
//    private KernelBrowser $client;
//    private EntityManagerInterface $manager;
//    private EntityRepository $repository;
//    private string $path = '/session/';
//
//    protected function setUp(): void
//    {
//        $this->client = static::createClient();
//        $this->manager = static::getContainer()->get('doctrine')->getManager();
//        $this->repository = $this->manager->getRepository(Session::class);
//
//        foreach ($this->repository->findAll() as $object) {
//            $this->manager->remove($object);
//        }
//
//        $this->manager->flush();
//    }
//
//    public function testIndex(): void
//    {
//        $crawler = $this->client->request('GET', $this->path);
//
//        self::assertResponseStatusCodeSame(200);
//        self::assertPageTitleContains('Session index');
//
//        // Use the $crawler to perform additional assertions e.g.
//        // self::assertSame('Some text on the page', $crawler->filter('.p')->first());
//    }
//
//    public function testNew(): void
//    {
//        $this->markTestIncomplete();
//        $this->client->request('GET', sprintf('%snew', $this->path));
//
//        self::assertResponseStatusCodeSame(200);
//
//        $this->client->submitForm('Save', [
//            'session[startTime]' => 'Testing',
//            'session[endTime]' => 'Testing',
//            'session[room]' => 'Testing',
//            'session[studyClass]' => 'Testing',
//            'session[teacher]' => 'Testing',
//        ]);
//
//        self::assertResponseRedirects($this->path);
//
//        self::assertSame(1, $this->repository->count([]));
//    }
//
//    public function testShow(): void
//    {
//        $this->markTestIncomplete();
//        $fixture = new Session();
//        $fixture->setStartTime('My Title');
//        $fixture->setEndTime('My Title');
//        $fixture->setRoom('My Title');
//        $fixture->setStudyClass('My Title');
//        $fixture->setTeacher('My Title');
//
//        $this->manager->persist($fixture);
//        $this->manager->flush();
//
//        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));
//
//        self::assertResponseStatusCodeSame(200);
//        self::assertPageTitleContains('Session');
//
//        // Use assertions to check that the properties are properly displayed.
//    }
//
//    public function testEdit(): void
//    {
//        $this->markTestIncomplete();
//        $fixture = new Session();
//        $fixture->setStartTime('Value');
//        $fixture->setEndTime('Value');
//        $fixture->setRoom('Value');
//        $fixture->setStudyClass('Value');
//        $fixture->setTeacher('Value');
//
//        $this->manager->persist($fixture);
//        $this->manager->flush();
//
//        $this->client->request('GET', sprintf('%s%s/edit', $this->path, $fixture->getId()));
//
//        $this->client->submitForm('Update', [
//            'session[startTime]' => 'Something New',
//            'session[endTime]' => 'Something New',
//            'session[room]' => 'Something New',
//            'session[studyClass]' => 'Something New',
//            'session[teacher]' => 'Something New',
//        ]);
//
//        self::assertResponseRedirects('/session/');
//
//        $fixture = $this->repository->findAll();
//
//        self::assertSame('Something New', $fixture[0]->getStartTime());
//        self::assertSame('Something New', $fixture[0]->getEndTime());
//        self::assertSame('Something New', $fixture[0]->getRoom());
//        self::assertSame('Something New', $fixture[0]->getStudyClass());
//        self::assertSame('Something New', $fixture[0]->getTeacher());
//    }
//
//    public function testRemove(): void
//    {
//        $this->markTestIncomplete();
//        $fixture = new Session();
//        $fixture->setStartTime('Value');
//        $fixture->setEndTime('Value');
//        $fixture->setRoom('Value');
//        $fixture->setStudyClass('Value');
//        $fixture->setTeacher('Value');
//
//        $this->manager->persist($fixture);
//        $this->manager->flush();
//
//        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));
//        $this->client->submitForm('Delete');
//
//        self::assertResponseRedirects('/session/');
//        self::assertSame(0, $this->repository->count([]));
//    }
//}
