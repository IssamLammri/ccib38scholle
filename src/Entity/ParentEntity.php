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
    #[Groups(['read_payment', 'read_parent'])]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 255)]
    #[Groups(['read_payment', 'read_parent'])]
    private ?string $fatherLastName = null;

    #[ORM\Column(type: 'string', length: 255)]
    #[Groups(['read_payment', 'read_parent'])]
    private ?string $fatherFirstName = null;

    #[ORM\Column(type: 'string', length: 255)]
    #[Groups(['read_payment', 'read_parent'])]
    private ?string $fatherEmail = null;

    #[ORM\Column(type: 'string', length: 20)]
    #[Groups(['read_payment', 'read_parent'])]
    private ?string $fatherPhone = null;

    #[ORM\Column(type: 'string', length: 255)]
    #[Groups(['read_payment', 'read_parent'])]
    private ?string $motherLastName = null;

    #[ORM\Column(type: 'string', length: 255)]
    #[Groups(['read_payment', 'read_parent'])]
    private ?string $motherFirstName = null;

    #[ORM\Column(type: 'string', length: 255)]
    #[Groups(['read_payment', 'read_parent'])]
    private ?string $motherEmail = null;

    #[ORM\Column(type: 'string', length: 20)]
    #[Groups(['read_payment', 'read_parent'])]
    private ?string $motherPhone = null;

    #[ORM\Column(type: 'string', length: 20)]
    private ?string $familyStatus = null; // Married or Divorced

    #[ORM\OneToMany(targetEntity: Student::class, mappedBy: 'parent', cascade: ['persist', 'remove'])]
    #[Groups(['read_payment', 'read_parent'])]
    private Collection $students;

    #[ORM\OneToMany(targetEntity: Invoice::class, mappedBy: 'parent', cascade: ['persist', 'remove'])]
    private Collection $invoices;

    #[ORM\OneToOne(targetEntity: User::class, inversedBy: 'parent', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', unique: false, nullable: true, onDelete: 'CASCADE')]
    private ?User $user = null;

    public function __construct()
    {
        $this->students = new ArrayCollection();
        $this->invoices = new ArrayCollection();
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

    public function setFatherPhone(?string $fatherPhone): self
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

    public function setMotherPhone(?string $motherPhone): self
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

    public function __toString(): string
    {
        if ($this->fatherLastName === 'Vide' || $this->fatherFirstName === 'Vide') {
            $father = '';
        }else{
            $father = $this->fatherLastName . ' ' . $this->fatherFirstName;
        }
        if ($this->motherLastName === 'Vide' || $this->motherFirstName === 'Vide') {
            $mother = '';
        } else {
            $mother = $this->motherLastName . ' ' . $this->motherFirstName;
        }
        return  $father . ' - ' . $mother;
    }

    #[Groups(['read_payment', 'read_parent', 'read_invoice'])]
    public function getFullNameParent(): string
    {
        $father = trim($this->fatherLastName . ' ' . $this->fatherFirstName);
        $mother = trim($this->motherLastName . ' ' . $this->motherFirstName);

        // Suppression de "Vide Vide"
        if ($father === 'Vide Vide') {
            $father = '';
        }
        if ($mother === 'Vide Vide') {
            $mother = '';
        }

        $parts = array_filter([$father, $mother]); // Filtre les valeurs vides

        return !empty($parts) ? implode(' - ', $parts) : '';
    }

    #[Groups(['read_payment', 'read_parent','read_invoice'])]
    public function getEmailContact(): string
    {
        return $this->fatherEmail ?? $this->motherEmail ?? 'ecole@ccib38.com';
    }

    #[Groups(['read_payment', 'read_parent','read_invoice'])]
    public function getPhoneContact(): string
    {
        return $this->fatherPhone ?? $this->motherPhone ?? '04 76 09 33 33';
    }

    public function getInvoices(): Collection
    {
        return $this->invoices;
    }

    public function addInvoice(Invoice $invoice): self
    {
        if (!$this->invoices->contains($invoice)) {
            $this->invoices[] = $invoice;
            $invoice->setParent($this);
        }

        return $this;
    }

    public function removeInvoice(Invoice $invoice): self
    {
        if ($this->invoices->removeElement($invoice)) {
            if ($invoice->getParent() === $this) {
                $invoice->setParent(null);
            }
        }

        return $this;
    }
    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(User $user): self
    {
        $this->user = $user;

        // tient la relation en synchro côté inverse
        if ($user->getParent() !== $this) {
            $user->setParent($this);
        }

        return $this;
    }
}
