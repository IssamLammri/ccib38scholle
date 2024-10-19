<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;


#[ORM\Entity]
class ParentEntity
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    #[Groups(['read_payment'])]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 255)]
    #[Groups(['read_payment'])]
    private ?string $fatherLastName = null;

    #[ORM\Column(type: 'string', length: 255)]
    #[Groups(['read_payment'])]
    private ?string $fatherFirstName = null;

    #[ORM\Column(type: 'string', length: 255)]
    #[Groups(['read_payment'])]
    private ?string $fatherEmail = null;

    #[ORM\Column(type: 'string', length: 20)]
    #[Groups(['read_payment'])]
    private ?string $fatherPhone = null;

    #[ORM\Column(type: 'string', length: 255)]
    #[Groups(['read_payment'])]
    private ?string $motherLastName = null;

    #[ORM\Column(type: 'string', length: 255)]
    #[Groups(['read_payment'])]
    private ?string $motherFirstName = null;

    #[ORM\Column(type: 'string', length: 255)]
    #[Groups(['read_payment'])]
    private ?string $motherEmail = null;

    #[ORM\Column(type: 'string', length: 20)]
    #[Groups(['read_payment'])]
    private ?string $motherPhone = null;

    #[ORM\Column(type: 'string', length: 20)]
    private ?string $familyStatus = null; // Married or Divorced

    #[ORM\OneToMany(targetEntity: Student::class, mappedBy: 'parent', cascade: ['persist', 'remove'])]
    #[Groups(['read_payment'])]
    private Collection $students;

    public function __construct()
    {
        $this->students = new ArrayCollection();
    }


    // Getters and Setters

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFatherLastName(): ?string
    {
        return $this->fatherLastName;
    }

    public function setFatherLastName(string $fatherLastName): self
    {
        $this->fatherLastName = $fatherLastName;

        return $this;
    }

    public function getFatherFirstName(): ?string
    {
        return $this->fatherFirstName;
    }

    public function setFatherFirstName(string $fatherFirstName): self
    {
        $this->fatherFirstName = $fatherFirstName;

        return $this;
    }

    public function getFatherEmail(): ?string
    {
        return $this->fatherEmail;
    }

    public function setFatherEmail(string $fatherEmail): self
    {
        $this->fatherEmail = $fatherEmail;

        return $this;
    }

    public function getFatherPhone(): ?string
    {
        return $this->fatherPhone;
    }

    public function setFatherPhone(string $fatherPhone): self
    {
        $this->fatherPhone = $fatherPhone;

        return $this;
    }

    public function getMotherLastName(): ?string
    {
        return $this->motherLastName;
    }

    public function setMotherLastName(string $motherLastName): self
    {
        $this->motherLastName = $motherLastName;

        return $this;
    }

    public function getMotherFirstName(): ?string
    {
        return $this->motherFirstName;
    }

    public function setMotherFirstName(string $motherFirstName): self
    {
        $this->motherFirstName = $motherFirstName;

        return $this;
    }

    public function getMotherEmail(): ?string
    {
        return $this->motherEmail;
    }

    public function setMotherEmail(string $motherEmail): self
    {
        $this->motherEmail = $motherEmail;

        return $this;
    }

    public function getMotherPhone(): ?string
    {
        return $this->motherPhone;
    }

    public function setMotherPhone(string $motherPhone): self
    {
        $this->motherPhone = $motherPhone;

        return $this;
    }

    public function getFamilyStatus(): ?string
    {
        return $this->familyStatus;
    }

    public function setFamilyStatus(string $familyStatus): self
    {
        $this->familyStatus = $familyStatus;

        return $this;
    }

    public function getStudents(): Collection
    {
        return $this->students;
    }

    public function addStudent(Student $student): self
    {
        if (!$this->students->contains($student)) {
            $this->students[] = $student;
            $student->setParent($this);
        }

        return $this;
    }

    public function removeStudent(Student $student): self
    {
        if ($this->students->removeElement($student)) {
            // set the owning side to null (unless already changed)
            if ($student->getParent() === $this) {
                $student->setParent(null);
            }
        }

        return $this;
    }

    #[Groups(['read_payment'])]
    public function  getFullNameParent(): string
    {
        return $this->fatherLastName . ' ' . $this->fatherFirstName . ' - ' . $this->motherLastName . ' ' . $this->motherFirstName;
    }
}
