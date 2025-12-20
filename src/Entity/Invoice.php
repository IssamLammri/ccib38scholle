<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity]
class Invoice
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    #[Groups(['read_invoice','statistic_dashboard','read_invoice_for_refund','read_refund'])]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: ParentEntity::class, inversedBy: 'invoices')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['read_invoice'])]
    private ?ParentEntity $parent = null;

    #[ORM\OneToMany(targetEntity: Payment::class, mappedBy: 'invoice', cascade: ['persist', 'remove'])]
    #[Groups(['read_invoice','read_invoice_for_refund','read_refund'])]
    private iterable $payments;

    #[ORM\Column(type: 'date')]
    #[Groups(['read_invoice','statistic_dashboard','read_invoice_for_refund','read_refund'])]
    private ?\DateTimeInterface $invoiceDate = null;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
    #[Groups(['read_invoice','statistic_dashboard','read_invoice_for_refund','read_refund'])]
    private ?string $totalAmount = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    #[Groups(['read_invoice','read_invoice_for_refund','read_refund'])]
    private ?string $comment = null;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2, nullable: true)]
    #[Groups(['read_invoice','statistic_dashboard'])]
    private ?string $discount = null;

    #[ORM\ManyToMany(targetEntity: Refund::class, mappedBy: 'invoices')]
    #[Groups(['read_invoice'])]
    private Collection $refunds;

    public function __construct()
    {
        $this->payments = new ArrayCollection();
        $this->refunds = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    #[Groups(['read_invoice'])]
    public function getPayments(): iterable
    {
        return $this->payments;
    }

    public function addPayment(Payment $payment): self
    {
        if (!$this->payments->contains($payment)) {
            $this->payments[] = $payment;
            $payment->setInvoice($this);
        }
        return $this;
    }

    public function removePayment(Payment $payment): self
    {
        if ($this->payments->removeElement($payment)) {
            if ($payment->getInvoice() === $this) {
                $payment->setInvoice(null);
            }
        }
        return $this;
    }

    public function getInvoiceDate(): ?\DateTimeInterface
    {
        return $this->invoiceDate;
    }

    public function setInvoiceDate(\DateTimeInterface $invoiceDate): self
    {
        $this->invoiceDate = $invoiceDate;
        return $this;
    }

    public function getTotalAmount(): ?string
    {
        return $this->totalAmount;
    }

    public function setTotalAmount(string $totalAmount): self
    {
        $this->totalAmount = $totalAmount;
        return $this;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(?string $comment): self
    {
        $this->comment = $comment;
        return $this;
    }

    public function getDiscount(): ?string
    {
        return $this->discount;
    }

    public function setDiscount(?string $discount): self
    {
        $this->discount = $discount;
        return $this;
    }

    /** @return Collection<int, Refund> */
    public function getRefunds(): Collection
    {
        return $this->refunds;
    }

    public function addRefund(Refund $refund): self
    {
        if (!$this->refunds->contains($refund)) {
            $this->refunds->add($refund);
        }
        return $this;
    }

    public function removeRefund(Refund $refund): self
    {
        $this->refunds->removeElement($refund);
        return $this;
    }
}
