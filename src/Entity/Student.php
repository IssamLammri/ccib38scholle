<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class Student
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $lastName = null;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $firstName = null;

    #[ORM\Column(type: 'date')]
    private ?\DateTimeInterface $birthDate = null;

    #[ORM\Column(type: 'array')]
    private array $previousClasses = [];

    #[ORM\Column(type: 'string', length: 10)]
    private ?string $gender = null;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $address = null;

    #[ORM\Column(type: 'string', length: 10)]
    private ?string $postalCode = null;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $city = null;

    #[ORM\Column(type: 'integer')]
    private ?int $level = null;

    #[ORM\OneToOne(inversedBy: 'student', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?ParentEntity $parent = null;

    // Getters and Setters

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): self
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): self
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getBirthDate(): ?\DateTimeInterface
    {
        return $this->birthDate;
    }

    public function setBirthDate(\DateTimeInterface $birthDate): self
    {
        $this->birthDate = $birthDate;

        return $this;
    }

    public function getPreviousClasses(): array
    {
        return $this->previousClasses;
    }

    public function setPreviousClasses(array $previousClasses): self
    {
        $this->previousClasses = $previousClasses;

        return $this;
    }

    public function getGender(): ?string
    {
        return $this->gender;
    }

    public function setGender(string $gender): self
    {
        $this->gender = $gender;

        return $this;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(string $address): self
    {
        $this->address = $address;

        return $this;
    }

    public function getPostalCode(): ?string
    {
        return $this->postalCode;
    }

    public function setPostalCode(string $postalCode): self
    {
        $this->postalCode = $postalCode;

        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(string $city): self
    {
        $this->city = $city;

        return $this;
    }

    public function getLevel(): ?int
    {
        return $this->level;
    }

    public function setLevel(int $level): self
    {
        $this->level = $level;

        return $this;
    }

    public function getParent(): ?ParentEntity
    {
        return $this->parent;
    }

    public function setParent(ParentEntity $parent): self
    {
        $this->parent = $parent;

        return $this;
    }

    public function __toString(): string
    {
        return $this->getLastName() . ' ' . $this->getFirstName();
    }
}
