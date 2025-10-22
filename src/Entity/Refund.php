<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity]
#[ORM\Table(name: 'refund')]
#[ORM\HasLifecycleCallbacks]
class Refund
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    #[Groups(['read_refund','statistic_dashboard'])]
    private ?int $id = null;

    // Parent remboursé
    #[ORM\ManyToOne(targetEntity: ParentEntity::class, inversedBy: 'refunds')]
    #[ORM\JoinColumn(name: 'parent_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    #[Groups(['read_refund','statistic_dashboard'])]
    private ?ParentEntity $parent = null;

    // Factures associées à ce remboursement (peut être vide)
    #[ORM\ManyToMany(targetEntity: Invoice::class, inversedBy: 'refunds')]
    #[ORM\JoinTable(name: 'refund_invoice')]
    #[Groups(['read_refund'])]
    private Collection $invoices;

    // Montant total remboursé (toutes factures confondues)
    #[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
    #[Groups(['read_refund','statistic_dashboard'])]
    private ?string $amount = null;

    // Date du remboursement (ou de la demande)
    #[ORM\Column(type: 'datetime')]
    #[Groups(['read_refund','statistic_dashboard'])]
    private ?\DateTimeInterface $refundDate = null;

    // Méthode (CB, virement, espèces, etc.)
    #[ORM\Column(type: 'string', length: 50, nullable: true)]
    #[Groups(['read_refund'])]
    private ?string $method = null;

    // Statut (pending, processed, canceled)
    #[ORM\Column(type: 'string', length: 20)]
    #[Groups(['read_refund','statistic_dashboard'])]
    private string $status = 'pending';

    // Raison/commentaire
    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    #[Groups(['read_refund'])]
    private ?string $comment = null;

    // Référence interne/banque
    #[ORM\Column(type: 'string', length: 100, nullable: true)]
    #[Groups(['read_refund'])]
    private ?string $reference = null;

    #[ORM\Column(type: 'datetime')]
    #[Groups(['read_refund'])]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(type: 'datetime')]
    #[Groups(['read_refund'])]
    private ?\DateTimeInterface $updatedAt = null;

    public function __construct()
    {
        $this->invoices = new ArrayCollection();
        $this->refundDate = new \DateTimeImmutable();
    }

    #[ORM\PrePersist]
    public function onPrePersist(): void
    {
        $now = new \DateTimeImmutable();
        $this->createdAt = $now;
        $this->updatedAt = $now;
        if (!$this->refundDate) {
            $this->refundDate = $now;
        }
    }

    #[ORM\PreUpdate]
    public function onPreUpdate(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }

    // Getters/Setters

    public function getId(): ?int { return $this->id; }

    public function getParent(): ?ParentEntity { return $this->parent; }
    public function setParent(?ParentEntity $parent): self { $this->parent = $parent; return $this; }

    /** @return Collection<int, Invoice> */
    public function getInvoices(): Collection { return $this->invoices; }

    public function addInvoice(Invoice $invoice): self
    {
        if (!$this->invoices->contains($invoice)) {
            $this->invoices->add($invoice);
            $invoice->addRefund($this); // garder la relation synchro
        }
        return $this;
    }

    public function removeInvoice(Invoice $invoice): self
    {
        if ($this->invoices->removeElement($invoice)) {
            $invoice->removeRefund($this);
        }
        return $this;
    }

    public function getAmount(): ?string { return $this->amount; }
    public function setAmount(string $amount): self { $this->amount = $amount; return $this; }

    public function getRefundDate(): ?\DateTimeInterface { return $this->refundDate; }
    public function setRefundDate(\DateTimeInterface $refundDate): self { $this->refundDate = $refundDate; return $this; }

    public function getMethod(): ?string { return $this->method; }
    public function setMethod(?string $method): self { $this->method = $method; return $this; }

    public function getStatus(): string { return $this->status; }
    public function setStatus(string $status): self { $this->status = $status; return $this; }

    public function getComment(): ?string { return $this->comment; }
    public function setComment(?string $comment): self { $this->comment = $comment; return $this; }

    public function getReference(): ?string { return $this->reference; }
    public function setReference(?string $reference): self { $this->reference = $reference; return $this; }

    public function getCreatedAt(): ?\DateTimeInterface { return $this->createdAt; }
    public function getUpdatedAt(): ?\DateTimeInterface { return $this->updatedAt; }
}
