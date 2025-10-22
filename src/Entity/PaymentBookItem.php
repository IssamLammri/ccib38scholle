<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity]
#[ORM\Table(name: 'payment_book_item')]
class PaymentBookItem
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['read_payment','read_invoice','read_student_class_registered','read_invoice_for_refund','read_refund'])]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Payment::class, inversedBy: 'bookItems')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Payment $payment = null;

    #[ORM\ManyToOne(targetEntity: Book::class)]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['read_payment','read_invoice','read_student_class_registered','read_invoice_for_refund','read_refund'])]
    private ?Book $book = null;

    // quantité achetée
    #[ORM\Column(type: 'integer')]
    #[Groups(['read_payment','read_invoice'])]
    private int $quantity = 1;

    // prix unitaire TTC gelé au moment de l’achat
    #[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
    #[Groups(['read_payment','read_invoice'])]
    private string $unitPrice = '0.00';

    // total de la ligne = quantity * unitPrice
    #[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
    #[Groups(['read_payment','read_invoice'])]
    private string $lineTotal = '0.00';

    public function getId(): ?int { return $this->id; }

    public function getPayment(): ?Payment { return $this->payment; }
    public function setPayment(?Payment $payment): self { $this->payment = $payment; return $this; }

    public function getBook(): ?Book { return $this->book; }
    public function setBook(?Book $book): self { $this->book = $book; return $this; }

    public function getQuantity(): int { return $this->quantity; }
    public function setQuantity(int $quantity): self { $this->quantity = max(1, $quantity); return $this; }

    public function getUnitPrice(): string { return $this->unitPrice; }
    public function setUnitPrice(string $unitPrice): self { $this->unitPrice = $unitPrice; return $this; }

    public function getLineTotal(): string { return $this->lineTotal; }
    public function setLineTotal(string $lineTotal): self { $this->lineTotal = $lineTotal; return $this; }
}
