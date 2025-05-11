<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity]
class Student
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    #[Groups(['read_student','read_study_class','read_student_class_registered','read_payment'])]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 255)]
    #[Groups(['read_student','read_payment','read_student_class_registered','student_session_read'])]
    private ?string $lastName = null;

    #[ORM\Column(type: 'string', length: 255)]
    #[Groups(['read_student','read_payment','read_student_class_registered','student_session_read'])]
    private ?string $firstName = null;

    #[ORM\Column(type: 'date')]
    #[Groups(['read_student','read_student_class_registered'])]
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

    #[ORM\Column(type: 'string')]
    #[Groups(['read_student','read_student_class_registered'])]
    private ?string $level = null;

    #[ORM\OneToMany(
        targetEntity: RegistrationArabicCours::class,
        mappedBy: 'student',
        cascade: ['persist', 'remove']
    )]
    private Collection $registrationArabicCours;

    #[ORM\ManyToOne(inversedBy: 'students')]
    #[ORM\JoinColumn(nullable: false)]
    private ?ParentEntity $parent = null;

    #[ORM\OneToMany(targetEntity: Payment::class, mappedBy: 'student')]
    private Collection $payments;

    #[ORM\OneToMany(targetEntity: StudentClassRegistered::class, mappedBy: 'student')]
    #[Groups(['read_payment'])]
    private Collection $registrations;

    public function __construct()
    {
        $this->registrations = new ArrayCollection();
        $this->registrationArabicCours = new ArrayCollection();
    }

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

    public function getLevel(): ?string
    {
        return $this->level;
    }

    public function setLevel(string $level): self
    {
        $this->level = $level;

        return $this;
    }

    public function getParent(): ?ParentEntity
    {
        return $this->parent;
    }

    public function setParent(?ParentEntity $parent): self
    {
        $this->parent = $parent;

        return $this;
    }

    public function __toString(): string
    {
        return $this->getLastName() . ' ' . $this->getFirstName();
    }


    /**
     * @return Collection<int, Payment>
     */
    public function getPayments(): Collection
    {
        return $this->payments;
    }

    public function addPayment(Payment $payment): self
    {
        if (!$this->payments->contains($payment)) {
            $this->payments[] = $payment;
            $payment->setStudent($this); // Relation inverse
        }

        return $this;
    }

    public function removePayment(Payment $payment): self
    {
        if ($this->payments->removeElement($payment)) {
            if ($payment->getStudent() === $this) {
                $payment->setStudent(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, StudentClassRegistered>
     */
    public function getRegistrations(): Collection
    {
        return $this->registrations;
    }

    public function addRegistration(StudentClassRegistered $registration): self
    {
        if (!$this->registrations->contains($registration)) {
            $this->registrations[] = $registration;
            $registration->setStudent($this); // Relation inverse
        }

        return $this;
    }

    public function removeRegistration(StudentClassRegistered $registration): self
    {
        if ($this->registrations->removeElement($registration)) {
            if ($registration->getStudent() === $this) {
                $registration->setStudent(null); // Relation inverse
            }
        }

        return $this;
    }

    #[Groups(['read_payment','read_student_class_registered','student_session_read','read_invoice'])]
    public function getLevelClass(): string
    {
        return match ($this->level) {
            '1' => 'CP',
            '2' => 'CE1',
            '3' => 'CE2',
            '4' => 'CM1',
            '5' => 'CM2',
            '6' => '6ème',
            '7' => '5ème',
            '8' => '4ème',
            '9' => '3ème',
            '10' => '2nde',
            '11' => '1ère',
            '12' => 'Terminale',
            default => $this->level,
        };
    }

    #[Groups(['read_invoice'])]
    public function getFullName(): string
    {
        return $this->getLastName() . ' ' . $this->getFirstName();
    }

    /**
     * @return Collection<int, RegistrationArabicCours>
     */
    public function getRegistrationArabicCours(): Collection
    {
        return $this->registrationArabicCours;
    }

    public function addRegistrationArabicCours(RegistrationArabicCours $registration): self
    {
        if (!$this->registrationArabicCours->contains($registration)) {
            $this->registrationArabicCours->add($registration);
            $registration->setStudent($this);
        }

        return $this;
    }

    public function removeRegistrationArabicCours(RegistrationArabicCours $registration): self
    {
        if ($this->registrationArabicCours->removeElement($registration)) {
            // assure la mise à jour du lien inverse
            if ($registration->getStudent() === $this) {
                $registration->setStudent(null);
            }
        }

        return $this;
    }
}
