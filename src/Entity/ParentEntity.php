<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class ParentEntity
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $fatherLastName = null;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $fatherFirstName = null;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $fatherEmail = null;

    #[ORM\Column(type: 'string', length: 20)]
    private ?string $fatherPhone = null;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $motherLastName = null;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $motherFirstName = null;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $motherEmail = null;

    #[ORM\Column(type: 'string', length: 20)]
    private ?string $motherPhone = null;

    #[ORM\Column(type: 'string', length: 20)]
    private ?string $familyStatus = null; // Married or Divorced

    #[ORM\OneToOne(mappedBy: 'parent', cascade: ['persist', 'remove'])]
    private ?Student $student = null;

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

    public function getStudent(): ?Student
    {
        return $this->student;
    }

    public function setStudent(?Student $student): self
    {
        // set (or unset) the owning side of the relation if necessary
        $newParent = null === $student ? null : $this;
        if ($student->getParent() !== $newParent) {
            $student->setParent($newParent);
        }

        $this->student = $student;

        return $this;
    }
}
