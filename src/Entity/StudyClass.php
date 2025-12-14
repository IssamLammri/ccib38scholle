<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
class StudyClass
{
    public const CLASS_TYPE_ARABE   = 'Arabe';
    public const CLASS_TYPE_SOUTIEN = 'Soutien scolaire';
    public const CLASS_TYPE_AUTRE   = 'Autre';

    public const DAY_SATURDAY  = 'Samedi';
    public const DAY_SUNDAY    = 'Dimanche';
    public const DAY_MONDAY    = 'Lundi';
    public const DAY_TUESDAY   = 'Mardi';
    public const DAY_WEDNESDAY = 'Mercredi';
    public const DAY_THURSDAY  = 'Jeudi';
    public const DAY_FRIDAY    = 'Vendredi';

    // Années scolaires autorisées
    public const SCHOOL_YEAR_2024_2025 = '2024/2025';
    public const SCHOOL_YEAR_2025_2026 = '2025/2026';
    public const SCHOOL_YEAR_ACTIVE = self::SCHOOL_YEAR_2025_2026;

    public const SCHOOL_YEARS = [
        self::SCHOOL_YEAR_2024_2025,
        self::SCHOOL_YEAR_2025_2026,
    ];

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    #[Groups(['read_payment','read_student','read_study_class','read_teacher','presence_session'])]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 255)]
    #[Groups(['read_payment','read_student','read_study_class','read_session','read_invoice','read_teacher','presence_session'])]
    private ?string $name = null;

    #[ORM\Column(type: 'string')]
    #[Groups(['read_study_class','read_session','presence_session'])]
    private ?string $level = null;

    #[ORM\Column(type: 'string', length: 255)]
    #[Groups(['read_study_class','read_session','read_payment','read_invoice','presence_session'])]
    private ?string $speciality = null;

    #[ORM\Column(type: 'string', length: 50)]
    #[Groups(['read_study_class','read_session'])]
    private ?string $day = self::DAY_SATURDAY;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    #[Groups(['read_study_class'])]
    private ?string $whatsappUrl = null;

    #[ORM\Column(type: 'time')]
    #[Groups(['read_study_class','read_session'])]
    private ?\DateTimeInterface $startHour = null;

    #[ORM\Column(type: 'time')]
    #[Groups(['read_study_class','read_session'])]
    private ?\DateTimeInterface $endHour = null;

    #[ORM\Column(type: 'string', length: 100)]
    #[Groups(['read_study_class','read_session'])]
    private ?string $classType = null; // Ex: Arabe, Soutien, Autre

    #[ORM\ManyToOne(targetEntity: Teacher::class, inversedBy: 'classes')]
    #[Groups(['read_study_class','read_session'])]
    private ?Teacher $principalTeacher = null;

    #[ORM\ManyToOne(targetEntity: Room::class)]
    #[ORM\JoinColumn(name: 'principal_room_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    #[Groups(['read_study_class','read_session'])]
    private ?Room $principalRoom = null;

    // >>> NOUVEAU : Année scolaire (2024/2025 ou 2025/2026)
    #[ORM\Column(type: 'string', length: 9)]
    #[Assert\Choice(choices: StudyClass::SCHOOL_YEARS, message: 'Année scolaire invalide.')]
    #[Groups(['read_study_class','read_session','read_payment','read_invoice'])]
    private ?string $schoolYear = self::SCHOOL_YEAR_2025_2026;

    #[ORM\OneToMany(targetEntity: Payment::class, mappedBy: 'studyClass')]
    private Collection $payments;

    public function __construct()
    {
        $this->payments = new ArrayCollection();
    }

    // Getters / Setters existants...

    public function getId(): ?int { return $this->id; }

    public function getName(): ?string { return $this->name; }
    public function setName(string $name): self { $this->name = $name; return $this; }

    public function getLevel(): ?string { return $this->level; }
    public function setLevel(string $level): self { $this->level = $level; return $this; }

    public function getSpeciality(): ?string { return $this->speciality; }
    public function setSpeciality(string $speciality): self { $this->speciality = $speciality; return $this; }

    /**
     * @return Collection<int, Payment>
     */
    public function getPayments(): Collection { return $this->payments; }

    public function addPayment(Payment $payment): self
    {
        if (!$this->payments->contains($payment)) {
            $this->payments[] = $payment;
            $payment->setStudyClass($this);
        }
        return $this;
    }

    public function removePayment(Payment $payment): self
    {
        if ($this->payments->removeElement($payment)) {
            if ($payment->getStudyClass() === $this) {
                $payment->setStudyClass(null);
            }
        }
        return $this;
    }

    public function __toString(): string
    {
        // On inclut la salle si dispo + l’année scolaire
        $room = $this->getPrincipalRoom()?->getName();
        $parts = array_filter([$this->getName(), $this->getSpeciality(), $room, $this->getSchoolYear()]);
        return implode(' - ', $parts);
    }

    #[Groups(['read_study_class','read_session'])]
    public function getLevelClass(): string { return $this->level; }

    public function getDay(): ?string { return $this->day; }
    public function setDay(?string $day): self { $this->day = $day; return $this; }

    public function getStartHour(): ?\DateTimeInterface { return $this->startHour; }
    public function setStartHour(?\DateTimeInterface $startHour): self { $this->startHour = $startHour; return $this; }

    public function getEndHour(): ?\DateTimeInterface { return $this->endHour; }
    public function setEndHour(?\DateTimeInterface $endHour): self { $this->endHour = $endHour; return $this; }

    public function getClassType(): ?string { return $this->classType; }
    public function setClassType(?string $classType): self { $this->classType = $classType; return $this; }

    public function getPrincipalTeacher(): ?Teacher { return $this->principalTeacher; }
    public function setPrincipalTeacher(?Teacher $principalTeacher): self { $this->principalTeacher = $principalTeacher; return $this; }

    // >>> Getters/Setters NOUVEAUX

    public function getPrincipalRoom(): ?Room
    {
        return $this->principalRoom;
    }

    public function setPrincipalRoom(?Room $principalRoom): self
    {
        $this->principalRoom = $principalRoom;
        return $this;
    }

    public function getSchoolYear(): ?string
    {
        return $this->schoolYear;
    }

    public function setSchoolYear(?string $schoolYear): self
    {
        $this->schoolYear = $schoolYear;
        return $this;
    }

    public function getWhatsappUrl(): ?string
    {
        return $this->whatsappUrl;
    }

    public function setWhatsappUrl(?string $whatsappUrl): self
    {
        $this->whatsappUrl = $whatsappUrl;
        return $this;
    }
}
