<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity]
class Payment
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    #[Groups(['read_payment','read_student_class_registered'])]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: ParentEntity::class)]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['read_payment','read_invoice'])]
    private ?ParentEntity $parent = null;

    #[ORM\ManyToOne(targetEntity: Student::class, inversedBy: 'payments')]
    #[ORM\JoinColumn(nullable: true)]
    #[Groups(['read_payment','read_invoice'])]
    private ?Student $student = null;

    #[ORM\ManyToOne(targetEntity: StudyClass::class, inversedBy: 'payments')]
    #[ORM\JoinColumn(nullable: true)]
    #[Groups(['read_payment','read_invoice'])]
    private ?StudyClass $studyClass = null;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
    #[Groups(['read_payment','read_invoice','statistic_dashboard'])]
    private ?string $amountPaid = null;

    #[ORM\Column(type: 'string', length: 255)]
    #[Groups(['read_payment','read_invoice','statistic_dashboard','read_student_class_registered'])]
    private ?string $serviceType = null;

    #[ORM\Column(type: 'date')]
    #[Groups(['read_payment','statistic_dashboard'])]
    private ?\DateTimeInterface $paymentDate = null;

    #[ORM\Column(type: 'string', length: 255)]
    #[Groups(['read_payment','read_invoice','statistic_dashboard'])]
    private ?string $paymentType = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    #[Groups(['read_payment','read_invoice','statistic_dashboard'])]
    private ?string $month = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    #[Groups(['read_payment','read_invoice','statistic_dashboard'])]
    private ?int $year = null;

    #[ORM\ManyToOne(targetEntity: Invoice::class, inversedBy: 'payments')]
    #[ORM\JoinColumn(nullable: true)]
    #[Groups(['read_payment','statistic_dashboard'])]
    private ?Invoice $invoice = null;


    #[ORM\Column(type: 'text', length: 255, nullable: true)]
    #[Groups(['read_payment','read_invoice'])]
    private ?string $comment= null;

    #[ORM\OneToMany(targetEntity: PaymentBookItem::class, mappedBy: 'payment', cascade: ['persist','remove'], orphanRemoval: true)]
    #[Groups(['read_payment','read_invoice','read_student_class_registered'])]
    private Collection $bookItems;


    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: true, onDelete: "SET NULL")]
    #[Groups(['read_payment','read_invoice','statistic_dashboard','read_student_class_registered'])]
    private ?User $processedBy = null;

    public function __construct()
    {
        $this->bookItems = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getParent(): ?ParentEntity
    {
        return $this->parent;
    }

    public function setParent(ParentEntity $parent): self
    {
        $this->parent = $parent;
        return $this;
    }

    public function getStudent(): ?Student
    {
        return $this->student;
    }

    public function setStudent(?Student $student): self
    {
        $this->student = $student;
        return $this;
    }

    public function getStudyClass(): ?StudyClass
    {
        return $this->studyClass;
    }

    public function setStudyClass(?StudyClass $studyClass): self
    {
        $this->studyClass = $studyClass;
        return $this;
    }

    public function getAmountPaid(): ?string
    {
        return $this->amountPaid;
    }

    public function setAmountPaid(string $amountPaid): self
    {
        $this->amountPaid = $amountPaid;
        return $this;
    }

    public function getPaymentDate(): ?\DateTimeInterface
    {
        return $this->paymentDate;
    }

    public function setPaymentDate(\DateTimeInterface $paymentDate): self
    {
        $this->paymentDate = $paymentDate;
        return $this;
    }

    public function getPaymentType(): ?string
    {
        return $this->paymentType;
    }

    public function setPaymentType(string $paymentType): self
    {
        $this->paymentType = $paymentType;
        return $this;
    }

    public function getMonth(): ?string
    {
        return $this->month;
    }

    public function setMonth(string $month): self
    {
        $this->month = $month;
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

    public function getServiceType(): ?string
    {
        return $this->serviceType;
    }

    public function setServiceType(string $serviceType): self
    {
        $this->serviceType = $serviceType;
        return $this;
    }
    public function getInvoice(): ?Invoice
    {
        return $this->invoice;
    }

    public function setInvoice(?Invoice $invoice): self
    {
        $this->invoice = $invoice;
        return $this;
    }

    public function getYear(): ?int
    {
        return $this->year;
    }

    public function setYear(int $year): self
    {
        $this->year = $year;
        return $this;
    }

    public function getBookItems(): Collection
    {
        return $this->bookItems;
    }

    public function addBookItem(PaymentBookItem $item): self
    {
        if (!$this->bookItems->contains($item)) {
            $this->bookItems->add($item);
            $item->setPayment($this);
        }
        return $this;
    }

    public function removeBookItem(PaymentBookItem $item): self
    {
        if ($this->bookItems->removeElement($item) && $item->getPayment() === $this) {
            $item->setPayment(null);
        }
        return $this;
    }

    public function getProcessedBy(): ?User
    {
        return $this->processedBy;
    }

    public function setProcessedBy(?User $user): self
    {
        $this->processedBy = $user;
        return $this;
    }
}
