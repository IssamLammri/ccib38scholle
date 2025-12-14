<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity]
class Teacher
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    #[Groups(['read_study_class','read_teacher','presence_session'])]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 255)]
    #[Groups(['read_session','read_study_class','read_teacher','presence_session'])]
    private ?string $lastName = null;

    #[ORM\Column(type: 'string', length: 255)]
    #[Groups(['read_session','read_study_class','read_teacher','presence_session'])]
    private ?string $firstName = null;

    #[ORM\Column(type: 'string', length: 255)]
    #[Groups(['read_teacher'])]
    private ?string $email = null;

    #[ORM\Column(type: 'string', length: 20)]
    #[Groups(['read_teacher'])]
    private ?string $phoneNumber = null;

    #[ORM\Column(type: 'string', length: 255)]
    #[Groups(['read_teacher'])]
    private ?string $educationLevel = null;

    #[ORM\Column(type: 'array')]
    #[Groups(['read_teacher'])]
    private array $specialities = [];

    #[ORM\OneToOne(targetEntity: User::class, cascade: ['persist'])]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?User $user = null;

    #[ORM\OneToMany(targetEntity: StudyClass::class, mappedBy: 'principalTeacher')]
    #[Groups(['read_teacher'])]
    private Collection $classes;


    public function __construct()
    {
        $this->classes = new ArrayCollection();
    }

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

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getPhoneNumber(): ?string
    {
        return $this->phoneNumber;
    }

    public function setPhoneNumber(string $phoneNumber): self
    {
        $this->phoneNumber = $phoneNumber;

        return $this;
    }

    public function getEducationLevel(): ?string
    {
        return $this->educationLevel;
    }

    public function setEducationLevel(string $educationLevel): self
    {
        $this->educationLevel = $educationLevel;

        return $this;
    }

    public function getSpecialities(): array
    {
        return $this->specialities;
    }

    public function setSpecialities(array $specialities): self
    {
        $this->specialities = $specialities;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(User $user): self
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @return Collection<int, StudyClass>
     */
    public function getClasses(): Collection
    {
        return $this->classes;
    }

    public function addClass(StudyClass $class): self
    {
        if (!$this->classes->contains($class)) {
            $this->classes[] = $class;
            $class->setPrincipalTeacher($this); // synchronise l’autre côté
        }
        return $this;
    }

    public function removeClass(StudyClass $class): self
    {
        if ($this->classes->removeElement($class)) {
            if ($class->getPrincipalTeacher() === $this) {
                $class->setPrincipalTeacher(null);
            }
        }
        return $this;
    }
}
