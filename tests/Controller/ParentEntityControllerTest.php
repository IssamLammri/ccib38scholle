<?php

namespace App\Test\Controller;

use App\Entity\ParentEntity;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ParentEntityControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $manager;
    private EntityRepository $repository;
    private string $path = '/parent/entity/';

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->manager = static::getContainer()->get('doctrine')->getManager();
        $this->repository = $this->manager->getRepository(ParentEntity::class);

        foreach ($this->repository->findAll() as $object) {
            $this->manager->remove($object);
        }

        $this->manager->flush();
    }

    public function testIndex(): void
    {
        $crawler = $this->client->request('GET', $this->path);

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('ParentEntity index');

        // Use the $crawler to perform additional assertions e.g.
        // self::assertSame('Some text on the page', $crawler->filter('.p')->first());
    }

    public function testNew(): void
    {
        $this->markTestIncomplete();
        $this->client->request('GET', sprintf('%snew', $this->path));

        self::assertResponseStatusCodeSame(200);

        $this->client->submitForm('Save', [
            'parent_entity[fatherLastName]' => 'Testing',
            'parent_entity[fatherFirstName]' => 'Testing',
            'parent_entity[fatherEmail]' => 'Testing',
            'parent_entity[fatherPhone]' => 'Testing',
            'parent_entity[motherLastName]' => 'Testing',
            'parent_entity[motherFirstName]' => 'Testing',
            'parent_entity[motherEmail]' => 'Testing',
            'parent_entity[motherPhone]' => 'Testing',
            'parent_entity[familyStatus]' => 'Testing',
            'parent_entity[student]' => 'Testing',
        ]);

        self::assertResponseRedirects($this->path);

        self::assertSame(1, $this->repository->count([]));
    }

    public function testShow(): void
    {
        $this->markTestIncomplete();
        $fixture = new ParentEntity();
        $fixture->setFatherLastName('My Title');
        $fixture->setFatherFirstName('My Title');
        $fixture->setFatherEmail('My Title');
        $fixture->setFatherPhone('My Title');
        $fixture->setMotherLastName('My Title');
        $fixture->setMotherFirstName('My Title');
        $fixture->setMotherEmail('My Title');
        $fixture->setMotherPhone('My Title');
        $fixture->setFamilyStatus('My Title');
        $fixture->setStudent('My Title');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('ParentEntity');

        // Use assertions to check that the properties are properly displayed.
    }

    public function testEdit(): void
    {
        $this->markTestIncomplete();
        $fixture = new ParentEntity();
        $fixture->setFatherLastName('Value');
        $fixture->setFatherFirstName('Value');
        $fixture->setFatherEmail('Value');
        $fixture->setFatherPhone('Value');
        $fixture->setMotherLastName('Value');
        $fixture->setMotherFirstName('Value');
        $fixture->setMotherEmail('Value');
        $fixture->setMotherPhone('Value');
        $fixture->setFamilyStatus('Value');
        $fixture->setStudent('Value');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s/edit', $this->path, $fixture->getId()));

        $this->client->submitForm('Update', [
            'parent_entity[fatherLastName]' => 'Something New',
            'parent_entity[fatherFirstName]' => 'Something New',
            'parent_entity[fatherEmail]' => 'Something New',
            'parent_entity[fatherPhone]' => 'Something New',
            'parent_entity[motherLastName]' => 'Something New',
            'parent_entity[motherFirstName]' => 'Something New',
            'parent_entity[motherEmail]' => 'Something New',
            'parent_entity[motherPhone]' => 'Something New',
            'parent_entity[familyStatus]' => 'Something New',
            'parent_entity[student]' => 'Something New',
        ]);

        self::assertResponseRedirects('/parent/entity/');

        $fixture = $this->repository->findAll();

        self::assertSame('Something New', $fixture[0]->getFatherLastName());
        self::assertSame('Something New', $fixture[0]->getFatherFirstName());
        self::assertSame('Something New', $fixture[0]->getFatherEmail());
        self::assertSame('Something New', $fixture[0]->getFatherPhone());
        self::assertSame('Something New', $fixture[0]->getMotherLastName());
        self::assertSame('Something New', $fixture[0]->getMotherFirstName());
        self::assertSame('Something New', $fixture[0]->getMotherEmail());
        self::assertSame('Something New', $fixture[0]->getMotherPhone());
        self::assertSame('Something New', $fixture[0]->getFamilyStatus());
        self::assertSame('Something New', $fixture[0]->getStudent());
    }

    public function testRemove(): void
    {
        $this->markTestIncomplete();
        $fixture = new ParentEntity();
        $fixture->setFatherLastName('Value');
        $fixture->setFatherFirstName('Value');
        $fixture->setFatherEmail('Value');
        $fixture->setFatherPhone('Value');
        $fixture->setMotherLastName('Value');
        $fixture->setMotherFirstName('Value');
        $fixture->setMotherEmail('Value');
        $fixture->setMotherPhone('Value');
        $fixture->setFamilyStatus('Value');
        $fixture->setStudent('Value');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));
        $this->client->submitForm('Delete');

        self::assertResponseRedirects('/parent/entity/');
        self::assertSame(0, $this->repository->count([]));
    }
}
