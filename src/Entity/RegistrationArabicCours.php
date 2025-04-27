<?php
// src/Entity/RegistrationArabicCours.php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: "App\Repository\RegistrationArabicCoursRepository")]
class RegistrationArabicCours
{
    #[ORM\Id, ORM\GeneratedValue, ORM\Column(type:"integer")]
    #[Groups(['read_registration'])]
    private ?int $id = null;

    #[ORM\Column(type:"string", length:32, unique:true)]
    #[Groups(['read_registration'])]
    private string $token;

    #[ORM\Column(type:"string", length:100)]
    #[Groups(['read_registration'])]
    private string $childFirstName;

    #[ORM\Column(type:"string", length:100)]
    #[Groups(['read_registration'])]
    private string $childLastName;

    #[ORM\Column(type:"date")]
    #[Groups(['read_registration'])]
    private \DateTimeInterface $childDob;

    #[ORM\Column(type:"string", length:10)]
    #[Groups(['read_registration'])]
    private string $childGender;

    #[ORM\Column(type:"string", length:50)]
    #[Groups(['read_registration'])]
    private string $childLevel;

    #[ORM\Column(type:"string", length:100)]
    #[Groups(['read_registration'])]
    private string $fatherFirstName;

    #[ORM\Column(type:"string", length:100)]
    #[Groups(['read_registration'])]
    private string $fatherLastName;

    #[ORM\Column(type:"string", length:100)]
    #[Groups(['read_registration'])]
    private string $motherFirstName;

    #[ORM\Column(type:"string", length:100)]
    #[Groups(['read_registration'])]
    private string $motherLastName;

    #[ORM\Column(type:"string", length:180)]
    #[Groups(['read_registration'])]
    private string $contactEmail;

    #[ORM\Column(type:"string", length:20)]
    #[Groups(['read_registration'])]
    private string $fatherPhone;

    #[ORM\Column(type:"string", length:20)]
    #[Groups(['read_registration'])]
    private string $motherPhone;

    #[ORM\Column(type:"string", length:255)]
    #[Groups(['read_registration'])]
    private string $address;

    #[ORM\Column(type:"string", length:10)]
    #[Groups(['read_registration'])]
    private string $postalCode;

    #[ORM\Column(type:"string", length:100)]
    #[Groups(['read_registration'])]
    private string $city;

    #[ORM\Column(type:"json")]
    #[Groups(['read_registration'])]
    private array $authorized = [];

    #[ORM\Column(type:"string", length:100, nullable:true)]
    #[Groups(['read_registration'])]
    private ?string $authorizedOtherName = null;

    #[ORM\Column(type:"string", length:50, nullable:true)]
    #[Groups(['read_registration'])]
    private ?string $authorizedOtherRelation = null;

    #[ORM\Column(type:"string", length:3)]
    #[Groups(['read_registration'])]
    private string $leaveAlone;

    #[ORM\Column(type:"string", length:3)]
    #[Groups(['read_registration'])]
    private string $imageRights;

    #[ORM\Column(type:"boolean")]
    #[Groups(['read_registration'])]
    private bool $commitmentSitu;

    #[ORM\Column(type:"string", length:3)]
    #[Groups(['read_registration'])]
    private string $medicalInfo;

    #[ORM\Column(type:"text", nullable:true)]
    #[Groups(['read_registration'])]
    private ?string $medicalDetails = null;

    #[ORM\Column(type:"string", length:3)]
    #[Groups(['read_registration'])]
    private string $medicalTreatment;

    #[ORM\Column(type:"boolean")]
    #[Groups(['read_registration'])]
    private bool $legalDeclaration;

    #[ORM\Column(type:"boolean")]
    #[Groups(['read_registration'])]
    private bool $paymentTerms;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    #[Groups(['read_registration'])]
    private ?string $childPhotoFilename = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    #[Groups(['read_registration'])]
    private ?string $wasEnrolled2024 = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    #[Groups(['read_registration'])]
    private ?string $previousLevel = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    #[Groups(['read_registration'])]
    private ?string $siblingEnrolled = null;

    #[ORM\Column(type:"datetime_immutable")]
    #[Groups(['read_registration'])]
    private \DateTimeImmutable $createdAt;

    public function __construct()
    {
        $this->token     = bin2hex(random_bytes(16));
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getToken(): string
    {
        return $this->token;
    }

    public function getChildFirstName(): string
    {
        return $this->childFirstName;
    }

    public function setChildFirstName(string $childFirstName): RegistrationArabicCours
    {
        $this->childFirstName = $childFirstName;
        return $this;
    }

    public function getChildLastName(): string
    {
        return $this->childLastName;
    }

    public function setChildLastName(string $childLastName): RegistrationArabicCours
    {
        $this->childLastName = $childLastName;
        return $this;
    }

    public function getChildDob(): \DateTimeInterface
    {
        return $this->childDob;
    }

    public function setChildDob(\DateTimeInterface $childDob): RegistrationArabicCours
    {
        $this->childDob = $childDob;
        return $this;
    }

    public function getChildGender(): string
    {
        return $this->childGender;
    }

    public function setChildGender(string $childGender): RegistrationArabicCours
    {
        $this->childGender = $childGender;
        return $this;
    }

    public function getChildLevel(): string
    {
        return $this->childLevel;
    }

    public function setChildLevel(string $childLevel): RegistrationArabicCours
    {
        $this->childLevel = $childLevel;
        return $this;
    }

    public function getFatherFirstName(): string
    {
        return $this->fatherFirstName;
    }

    public function setFatherFirstName(string $fatherFirstName): RegistrationArabicCours
    {
        $this->fatherFirstName = $fatherFirstName;
        return $this;
    }

    public function getFatherLastName(): string
    {
        return $this->fatherLastName;
    }

    public function setFatherLastName(string $fatherLastName): RegistrationArabicCours
    {
        $this->fatherLastName = $fatherLastName;
        return $this;
    }

    public function getMotherFirstName(): string
    {
        return $this->motherFirstName;
    }

    public function setMotherFirstName(string $motherFirstName): RegistrationArabicCours
    {
        $this->motherFirstName = $motherFirstName;
        return $this;
    }

    public function getMotherLastName(): string
    {
        return $this->motherLastName;
    }

    public function setMotherLastName(string $motherLastName): RegistrationArabicCours
    {
        $this->motherLastName = $motherLastName;
        return $this;
    }

    public function getContactEmail(): string
    {
        return $this->contactEmail;
    }

    public function setContactEmail(string $contactEmail): RegistrationArabicCours
    {
        $this->contactEmail = $contactEmail;
        return $this;
    }

    public function getFatherPhone(): string
    {
        return $this->fatherPhone;
    }

    public function setFatherPhone(string $fatherPhone): RegistrationArabicCours
    {
        $this->fatherPhone = $fatherPhone;
        return $this;
    }

    public function getMotherPhone(): string
    {
        return $this->motherPhone;
    }

    public function setMotherPhone(string $motherPhone): RegistrationArabicCours
    {
        $this->motherPhone = $motherPhone;
        return $this;
    }

    public function getAddress(): string
    {
        return $this->address;
    }

    public function setAddress(string $address): RegistrationArabicCours
    {
        $this->address = $address;
        return $this;
    }

    public function getPostalCode(): string
    {
        return $this->postalCode;
    }

    public function setPostalCode(string $postalCode): RegistrationArabicCours
    {
        $this->postalCode = $postalCode;
        return $this;
    }

    public function getCity(): string
    {
        return $this->city;
    }

    public function setCity(string $city): RegistrationArabicCours
    {
        $this->city = $city;
        return $this;
    }

    public function getAuthorized(): array
    {
        return $this->authorized;
    }

    public function setAuthorized(array $authorized): RegistrationArabicCours
    {
        $this->authorized = $authorized;
        return $this;
    }

    public function getAuthorizedOtherName(): ?string
    {
        return $this->authorizedOtherName;
    }

    public function setAuthorizedOtherName(?string $authorizedOtherName): RegistrationArabicCours
    {
        $this->authorizedOtherName = $authorizedOtherName;
        return $this;
    }

    public function getAuthorizedOtherRelation(): ?string
    {
        return $this->authorizedOtherRelation;
    }

    public function setAuthorizedOtherRelation(?string $authorizedOtherRelation): RegistrationArabicCours
    {
        $this->authorizedOtherRelation = $authorizedOtherRelation;
        return $this;
    }

    public function getLeaveAlone(): string
    {
        return $this->leaveAlone;
    }

    public function setLeaveAlone(string $leaveAlone): RegistrationArabicCours
    {
        $this->leaveAlone = $leaveAlone;
        return $this;
    }

    public function getImageRights(): string
    {
        return $this->imageRights;
    }

    public function setImageRights(string $imageRights): RegistrationArabicCours
    {
        $this->imageRights = $imageRights;
        return $this;
    }

    public function isCommitmentSitu(): bool
    {
        return $this->commitmentSitu;
    }

    public function setCommitmentSitu(bool $commitmentSitu): RegistrationArabicCours
    {
        $this->commitmentSitu = $commitmentSitu;
        return $this;
    }

    public function getMedicalInfo(): string
    {
        return $this->medicalInfo;
    }

    public function setMedicalInfo(string $medicalInfo): RegistrationArabicCours
    {
        $this->medicalInfo = $medicalInfo;
        return $this;
    }

    public function getMedicalDetails(): ?string
    {
        return $this->medicalDetails;
    }

    public function setMedicalDetails(?string $medicalDetails): RegistrationArabicCours
    {
        $this->medicalDetails = $medicalDetails;
        return $this;
    }

    public function getMedicalTreatment(): string
    {
        return $this->medicalTreatment;
    }

    public function setMedicalTreatment(string $medicalTreatment): RegistrationArabicCours
    {
        $this->medicalTreatment = $medicalTreatment;
        return $this;
    }

    public function isLegalDeclaration(): bool
    {
        return $this->legalDeclaration;
    }

    public function setLegalDeclaration(bool $legalDeclaration): RegistrationArabicCours
    {
        $this->legalDeclaration = $legalDeclaration;
        return $this;
    }

    public function isPaymentTerms(): bool
    {
        return $this->paymentTerms;
    }

    public function setPaymentTerms(bool $paymentTerms): RegistrationArabicCours
    {
        $this->paymentTerms = $paymentTerms;
        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): RegistrationArabicCours
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getChildPhotoFilename(): ?string
    {
        return $this->childPhotoFilename;
    }

    public function setChildPhotoFilename(?string $filename): self
    {
        $this->childPhotoFilename = $filename;
        return $this;
    }

    public function getWasEnrolled2024(): ?string
    {
        return $this->wasEnrolled2024;
    }

    public function setWasEnrolled2024(?string $wasEnrolled2024): RegistrationArabicCours
    {
        $this->wasEnrolled2024 = $wasEnrolled2024;
        return $this;
    }

    public function getPreviousLevel(): ?string
    {
        return $this->previousLevel;
    }

    public function setPreviousLevel(?string $previousLevel): RegistrationArabicCours
    {
        $this->previousLevel = $previousLevel;
        return $this;
    }

    public function getSiblingEnrolled(): ?string
    {
        return $this->siblingEnrolled;
    }

    public function setSiblingEnrolled(?string $siblingEnrolled): RegistrationArabicCours
    {
        $this->siblingEnrolled = $siblingEnrolled;
        return $this;
    }

}
