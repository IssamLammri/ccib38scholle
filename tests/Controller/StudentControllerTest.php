<?php

namespace App\Test\Controller;

use App\Entity\Student;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class StudentControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $manager;
    private EntityRepository $repository;
    private string $path = '/student/';

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->manager = static::getContainer()->get('doctrine')->getManager();
        $this->repository = $this->manager->getRepository(Student::class);

        foreach ($this->repository->findAll() as $object) {
            $this->manager->remove($object);
        }

        $this->manager->flush();
    }

    public function testIndex(): void
    {
        $crawler = $this->client->request('GET', $this->path);

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Student index');

        // Use the $crawler to perform additional assertions e.g.
        // self::assertSame('Some text on the page', $crawler->filter('.p')->first());
    }

    public function testNew(): void
    {
        $this->markTestIncomplete();
        $this->client->request('GET', sprintf('%snew', $this->path));

        self::assertResponseStatusCodeSame(200);

        $this->client->submitForm('Save', [
            'student[lastName]' => 'Testing',
            'student[firstName]' => 'Testing',
            'student[birthDate]' => 'Testing',
            'student[previousClasses]' => 'Testing',
            'student[gender]' => 'Testing',
            'student[address]' => 'Testing',
            'student[postalCode]' => 'Testing',
            'student[city]' => 'Testing',
            'student[level]' => 'Testing',
            'student[parent]' => 'Testing',
        ]);

        self::assertResponseRedirects($this->path);

        self::assertSame(1, $this->repository->count([]));
    }

    public function testShow(): void
    {
        $this->markTestIncomplete();
        $fixture = new Student();
        $fixture->setLastName('My Title');
        $fixture->setFirstName('My Title');
        $fixture->setBirthDate('My Title');
        $fixture->setPreviousClasses('My Title');
        $fixture->setGender('My Title');
        $fixture->setAddress('My Title');
        $fixture->setPostalCode('My Title');
        $fixture->setCity('My Title');
        $fixture->setLevel('My Title');
        $fixture->setParent('My Title');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Student');

        // Use assertions to check that the properties are properly displayed.
    }

    public function testEdit(): void
    {
        $this->markTestIncomplete();
        $fixture = new Student();
        $fixture->setLastName('Value');
        $fixture->setFirstName('Value');
        $fixture->setBirthDate('Value');
        $fixture->setPreviousClasses('Value');
        $fixture->setGender('Value');
        $fixture->setAddress('Value');
        $fixture->setPostalCode('Value');
        $fixture->setCity('Value');
        $fixture->setLevel('Value');
        $fixture->setParent('Value');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s/edit', $this->path, $fixture->getId()));

        $this->client->submitForm('Update', [
            'student[lastName]' => 'Something New',
            'student[firstName]' => 'Something New',
            'student[birthDate]' => 'Something New',
            'student[previousClasses]' => 'Something New',
            'student[gender]' => 'Something New',
            'student[address]' => 'Something New',
            'student[postalCode]' => 'Something New',
            'student[city]' => 'Something New',
            'student[level]' => 'Something New',
            'student[parent]' => 'Something New',
        ]);

        self::assertResponseRedirects('/student/');

        $fixture = $this->repository->findAll();

        self::assertSame('Something New', $fixture[0]->getLastName());
        self::assertSame('Something New', $fixture[0]->getFirstName());
        self::assertSame('Something New', $fixture[0]->getBirthDate());
        self::assertSame('Something New', $fixture[0]->getPreviousClasses());
        self::assertSame('Something New', $fixture[0]->getGender());
        self::assertSame('Something New', $fixture[0]->getAddress());
        self::assertSame('Something New', $fixture[0]->getPostalCode());
        self::assertSame('Something New', $fixture[0]->getCity());
        self::assertSame('Something New', $fixture[0]->getLevel());
        self::assertSame('Something New', $fixture[0]->getParent());
    }

    public function testRemove(): void
    {
        $this->markTestIncomplete();
        $fixture = new Student();
        $fixture->setLastName('Value');
        $fixture->setFirstName('Value');
        $fixture->setBirthDate('Value');
        $fixture->setPreviousClasses('Value');
        $fixture->setGender('Value');
        $fixture->setAddress('Value');
        $fixture->setPostalCode('Value');
        $fixture->setCity('Value');
        $fixture->setLevel('Value');
        $fixture->setParent('Value');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));
        $this->client->submitForm('Delete');

        self::assertResponseRedirects('/student/');
        self::assertSame(0, $this->repository->count([]));
    }
}
