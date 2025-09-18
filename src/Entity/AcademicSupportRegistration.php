<?php

namespace App\Entity;

use App\Repository\AcademicSupportRegistrationRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

#[ORM\Entity(repositoryClass: AcademicSupportRegistrationRepository::class)]
#[ORM\Table(name: 'academic_support_registration')]
#[ORM\HasLifecycleCallbacks]
class AcademicSupportRegistration
{

    const EN_COURS = 'en_cours'; // en cours de traitement
    const INSCRIT  = 'inscrit';
    const ANNULE   = 'annule';


    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    #[Groups(['read_list_registration_academic_support'])]
    private ?int $id = null;

    // Permet un accès public (lien de suivi) sans exposer l'id auto
    #[ORM\Column(length: 64, unique: true)]
    private string $publicToken = '';

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank]
    #[Groups(['read_list_registration_academic_support'])]
    private string $studentFirstName = '';

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank]
    #[Groups(['read_list_registration_academic_support'])]
    private string $studentLastName = '';

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank]
    #[Groups(['read_list_registration_academic_support'])]
    private string $level = '';

    // Liste des matières (JSON)
    #[ORM\Column(type: 'json')]
    #[Assert\NotBlank]
    #[Assert\Count(min: 1, minMessage: 'Sélectionnez au moins une matière.')]
    #[Groups(['read_list_registration_academic_support'])]
    private array $subjects = [];

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank]
    #[Groups(['read_list_registration_academic_support'])]
    private string $parentFirstName = '';

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank]
    #[Groups(['read_list_registration_academic_support'])]
    private string $parentLastName = '';

    #[ORM\Column(length: 150)]
    #[Assert\NotBlank]
    #[Assert\Email]
    #[Groups(['read_list_registration_academic_support'])]
    private string $email = '';

    #[ORM\Column(length: 30, nullable: true)]
    #[Groups(['read_list_registration_academic_support'])]
    private ?string $phone = null;

    #[ORM\Column(length: 100, nullable: true)]
    #[Groups(['read_list_registration_academic_support'])]
    private ?string $motherFirstName = null;

    #[ORM\Column(length: 100, nullable: true)]
    #[Groups(['read_list_registration_academic_support'])]
    private ?string $motherLastName = null;

    #[ORM\Column(length: 30, nullable: true)]
    #[Groups(['read_list_registration_academic_support'])]
    private ?string $motherPhone = null;

    #[ORM\Column(type: 'boolean')]
    #[Assert\IsTrue(message: 'Vous devez accepter la tarification et les conditions de paiement.')]
    private bool $acceptedPaymentTerms = false;

    // Statut interne (optionnel)
    #[ORM\Column(length: 20)]
    #[Groups(['read_list_registration_academic_support'])]
    private string $status = self::EN_COURS;

    #[ORM\Column(type: 'datetime_immutable')]
    #[Groups(['read_list_registration_academic_support'])]
    private \DateTimeImmutable $createdAt;


    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['read_list_registration_academic_support'])]
    private ?string $address = null;

    #[ORM\Column(length: 12, nullable: true)]
    #[Assert\Regex(pattern: '/^\d{5}$/', message: 'Code postal invalide (5 chiffres).')]
    #[Groups(['read_list_registration_academic_support'])]
    private ?string $postalCode = null;

    #[ORM\Column(length: 150, nullable: true)]
    #[Groups(['read_list_registration_academic_support'])]
    private ?string $city = null;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\Column(type: 'date_immutable', nullable: true)]
    #[Assert\NotBlank(message: 'La date de naissance est obligatoire.')]
    #[Assert\LessThanOrEqual('today', message: 'La date de naissance ne peut pas être dans le futur.')]
    #[Groups(['read_list_registration_academic_support'])]
    private ?\DateTimeImmutable $studentBirthDate = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        // token lisible URL
        $this->publicToken = bin2hex(random_bytes(16));
    }

    #[ORM\PreUpdate]
    public function touch(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }

    // Validation custom : au moins un téléphone (parent ou mère)
    #[Assert\Callback]
    public function validatePhones(ExecutionContextInterface $context): void
    {
        if (empty($this->phone) && empty($this->motherPhone)) {
            $context
                ->buildViolation('Veuillez renseigner au moins un numéro de téléphone (parent ou mère).')
                ->atPath('phone')
                ->addViolation();
        }
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): AcademicSupportRegistration
    {
        $this->id = $id;
        return $this;
    }

    public function getPublicToken(): string
    {
        return $this->publicToken;
    }

    public function setPublicToken(string $publicToken): AcademicSupportRegistration
    {
        $this->publicToken = $publicToken;
        return $this;
    }

    public function getStudentFirstName(): string
    {
        return $this->studentFirstName;
    }

    public function setStudentFirstName(string $studentFirstName): AcademicSupportRegistration
    {
        $this->studentFirstName = $studentFirstName;
        return $this;
    }

    public function getStudentLastName(): string
    {
        return $this->studentLastName;
    }

    public function setStudentLastName(string $studentLastName): AcademicSupportRegistration
    {
        $this->studentLastName = $studentLastName;
        return $this;
    }

    public function getLevel(): string
    {
        return $this->level;
    }

    public function setLevel(string $level): AcademicSupportRegistration
    {
        $this->level = $level;
        return $this;
    }

    public function getSubjects(): array
    {
        return $this->subjects;
    }

    public function setSubjects(array $subjects): AcademicSupportRegistration
    {
        $this->subjects = $subjects;
        return $this;
    }

    public function getParentFirstName(): string
    {
        return $this->parentFirstName;
    }

    public function setParentFirstName(string $parentFirstName): AcademicSupportRegistration
    {
        $this->parentFirstName = $parentFirstName;
        return $this;
    }

    public function getParentLastName(): string
    {
        return $this->parentLastName;
    }

    public function setParentLastName(string $parentLastName): AcademicSupportRegistration
    {
        $this->parentLastName = $parentLastName;
        return $this;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): AcademicSupportRegistration
    {
        $this->email = $email;
        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): AcademicSupportRegistration
    {
        $this->phone = $phone;
        return $this;
    }

    public function getMotherFirstName(): ?string
    {
        return $this->motherFirstName;
    }

    public function setMotherFirstName(?string $motherFirstName): AcademicSupportRegistration
    {
        $this->motherFirstName = $motherFirstName;
        return $this;
    }

    public function getMotherLastName(): ?string
    {
        return $this->motherLastName;
    }

    public function setMotherLastName(?string $motherLastName): AcademicSupportRegistration
    {
        $this->motherLastName = $motherLastName;
        return $this;
    }

    public function getMotherPhone(): ?string
    {
        return $this->motherPhone;
    }

    public function setMotherPhone(?string $motherPhone): AcademicSupportRegistration
    {
        $this->motherPhone = $motherPhone;
        return $this;
    }

    public function isAcceptedPaymentTerms(): bool
    {
        return $this->acceptedPaymentTerms;
    }

    public function setAcceptedPaymentTerms(bool $acceptedPaymentTerms): AcademicSupportRegistration
    {
        $this->acceptedPaymentTerms = $acceptedPaymentTerms;
        return $this;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): AcademicSupportRegistration
    {
        $this->status = $status;
        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): AcademicSupportRegistration
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeImmutable $updatedAt): AcademicSupportRegistration
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(?string $address): AcademicSupportRegistration
    {
        $this->address = $address;
        return $this;
    }

    public function getPostalCode(): ?string
    {
        return $this->postalCode;
    }

    public function setPostalCode(?string $postalCode): AcademicSupportRegistration
    {
        $this->postalCode = $postalCode;
        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(?string $city): AcademicSupportRegistration
    {
        $this->city = $city;
        return $this;
    }

    public function getStudentBirthDate(): ?\DateTimeImmutable
    {
        return $this->studentBirthDate;
    }

    public function setStudentBirthDate(?\DateTimeImmutable $studentBirthDate): AcademicSupportRegistration
    {
        $this->studentBirthDate = $studentBirthDate;
        return $this;
    }
}
