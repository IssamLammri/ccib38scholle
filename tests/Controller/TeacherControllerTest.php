<?php

namespace App\Test\Controller;

use App\Entity\Teacher;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class TeacherControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $manager;
    private EntityRepository $repository;
    private string $path = '/teacher/';

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->manager = static::getContainer()->get('doctrine')->getManager();
        $this->repository = $this->manager->getRepository(Teacher::class);

        foreach ($this->repository->findAll() as $object) {
            $this->manager->remove($object);
        }

        $this->manager->flush();
    }

    public function testIndex(): void
    {
        $crawler = $this->client->request('GET', $this->path);

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Teacher index');

        // Use the $crawler to perform additional assertions e.g.
        // self::assertSame('Some text on the page', $crawler->filter('.p')->first());
    }

    public function testNew(): void
    {
        $this->markTestIncomplete();
        $this->client->request('GET', sprintf('%snew', $this->path));

        self::assertResponseStatusCodeSame(200);

        $this->client->submitForm('Save', [
            'teacher[lastName]' => 'Testing',
            'teacher[firstName]' => 'Testing',
            'teacher[email]' => 'Testing',
            'teacher[phoneNumber]' => 'Testing',
            'teacher[educationLevel]' => 'Testing',
            'teacher[specialities]' => 'Testing',
        ]);

        self::assertResponseRedirects($this->path);

        self::assertSame(1, $this->repository->count([]));
    }

    public function testShow(): void
    {
        $this->markTestIncomplete();
        $fixture = new Teacher();
        $fixture->setLastName('My Title');
        $fixture->setFirstName('My Title');
        $fixture->setEmail('My Title');
        $fixture->setPhoneNumber('My Title');
        $fixture->setEducationLevel('My Title');
        $fixture->setSpecialities('My Title');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Teacher');

        // Use assertions to check that the properties are properly displayed.
    }

    public function testEdit(): void
    {
        $this->markTestIncomplete();
        $fixture = new Teacher();
        $fixture->setLastName('Value');
        $fixture->setFirstName('Value');
        $fixture->setEmail('Value');
        $fixture->setPhoneNumber('Value');
        $fixture->setEducationLevel('Value');
        $fixture->setSpecialities('Value');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s/edit', $this->path, $fixture->getId()));

        $this->client->submitForm('Update', [
            'teacher[lastName]' => 'Something New',
            'teacher[firstName]' => 'Something New',
            'teacher[email]' => 'Something New',
            'teacher[phoneNumber]' => 'Something New',
            'teacher[educationLevel]' => 'Something New',
            'teacher[specialities]' => 'Something New',
        ]);

        self::assertResponseRedirects('/teacher/');

        $fixture = $this->repository->findAll();

        self::assertSame('Something New', $fixture[0]->getLastName());
        self::assertSame('Something New', $fixture[0]->getFirstName());
        self::assertSame('Something New', $fixture[0]->getEmail());
        self::assertSame('Something New', $fixture[0]->getPhoneNumber());
        self::assertSame('Something New', $fixture[0]->getEducationLevel());
        self::assertSame('Something New', $fixture[0]->getSpecialities());
    }

    public function testRemove(): void
    {
        $this->markTestIncomplete();
        $fixture = new Teacher();
        $fixture->setLastName('Value');
        $fixture->setFirstName('Value');
        $fixture->setEmail('Value');
        $fixture->setPhoneNumber('Value');
        $fixture->setEducationLevel('Value');
        $fixture->setSpecialities('Value');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));
        $this->client->submitForm('Delete');

        self::assertResponseRedirects('/teacher/');
        self::assertSame(0, $this->repository->count([]));
    }
}
