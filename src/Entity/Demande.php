<?php
// src/Entity/Demande.php
namespace App\Entity;

use App\Repository\DemandeRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: DemandeRepository::class)]
class Demande
{
    public const STATUS_PENDING   = 'pending';
    public const STATUS_APPROVED  = 'approved';
    public const STATUS_REJECTED  = 'rejected';

    public const TYPE_CLASS_CHANGE = 'class_change'; // “Changement de classe”, etc.

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    #[Groups(['read_demande'])]
    private ?int $id = null;

    #[ORM\Column(type: 'datetime_immutable')]
    #[Groups(['read_demande'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\ManyToOne(targetEntity: ParentEntity::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?ParentEntity $parent = null;

    #[ORM\ManyToOne(targetEntity: Student::class)]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    #[Groups(['read_demande'])]
    private ?Student $student = null;

    #[ORM\Column(type: 'string', length: 50)]
    #[Groups(['read_demande'])]
    private string $type = self::TYPE_CLASS_CHANGE;

    #[ORM\Column(type: 'string', length: 20)]
    #[Groups(['read_demande'])]
    private string $status = self::STATUS_PENDING;

    // Texte libre : motif/raison (“Conflit avec activité sportive”)
    #[ORM\Column(type: 'text', nullable: true)]
    #[Groups(['read_demande'])]
    private ?string $comment = null;

    // Cible éventuelle (ex: nouvelle classe souhaitée)
    #[ORM\ManyToOne(targetEntity: StudyClass::class)]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    #[Groups(['read_demande'])]
    private ?StudyClass $targetStudyClass = null;

    public function __construct(ParentEntity $parent, ?Student $student = null)
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->parent    = $parent;
        $this->student   = $student;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): Demande
    {
        $this->id = $id;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(?\DateTimeImmutable $createdAt): Demande
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getParent(): ?ParentEntity
    {
        return $this->parent;
    }

    public function setParent(?ParentEntity $parent): Demande
    {
        $this->parent = $parent;
        return $this;
    }

    public function getStudent(): ?Student
    {
        return $this->student;
    }

    public function setStudent(?Student $student): Demande
    {
        $this->student = $student;
        return $this;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): Demande
    {
        $this->type = $type;
        return $this;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): Demande
    {
        $this->status = $status;
        return $this;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(?string $comment): Demande
    {
        $this->comment = $comment;
        return $this;
    }

    public function getTargetStudyClass(): ?StudyClass
    {
        return $this->targetStudyClass;
    }

    public function setTargetStudyClass(?StudyClass $targetStudyClass): Demande
    {
        $this->targetStudyClass = $targetStudyClass;
        return $this;
    }
}
