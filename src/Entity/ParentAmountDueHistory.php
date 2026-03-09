<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity]
class ParentAmountDueHistory
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    #[Groups(['read_payment', 'read_parent'])]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: ParentEntity::class, inversedBy: 'amountDueHistories')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?ParentEntity $parent = null;

    #[ORM\Column(type: 'string', length: 50)]
    #[Groups(['read_payment', 'read_parent'])]
    private string $field; // 'amountDueArabic' | 'amountDueSoutien'

    #[ORM\Column(type: 'integer')]
    #[Groups(['read_payment', 'read_parent'])]
    private int $oldValue;

    #[ORM\Column(type: 'integer')]
    #[Groups(['read_payment', 'read_parent'])]
    private int $newValue;

    #[ORM\Column(type: 'text', nullable: true)]
    #[Groups(['read_payment', 'read_parent'])]
    private ?string $comment = null;

    #[ORM\Column(type: 'datetime_immutable')]
    #[Groups(['read_payment', 'read_parent'])]
    private \DateTimeImmutable $createdAt;

    // Optionnel (très utile) : qui a modifié
    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?User $changedBy = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): ParentAmountDueHistory
    {
        $this->id = $id;
        return $this;
    }

    public function getParent(): ?ParentEntity
    {
        return $this->parent;
    }

    public function setParent(?ParentEntity $parent): ParentAmountDueHistory
    {
        $this->parent = $parent;
        return $this;
    }

    public function getField(): string
    {
        return $this->field;
    }

    public function setField(string $field): ParentAmountDueHistory
    {
        $this->field = $field;
        return $this;
    }

    public function getOldValue(): int
    {
        return $this->oldValue;
    }

    public function setOldValue(int $oldValue): ParentAmountDueHistory
    {
        $this->oldValue = $oldValue;
        return $this;
    }

    public function getNewValue(): int
    {
        return $this->newValue;
    }

    public function setNewValue(int $newValue): ParentAmountDueHistory
    {
        $this->newValue = $newValue;
        return $this;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(?string $comment): ParentAmountDueHistory
    {
        $this->comment = $comment;
        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): ParentAmountDueHistory
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getChangedBy(): ?User
    {
        return $this->changedBy;
    }

    public function setChangedBy(?User $changedBy): ParentAmountDueHistory
    {
        $this->changedBy = $changedBy;
        return $this;
    }
}