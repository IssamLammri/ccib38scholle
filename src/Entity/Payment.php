<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity]
class Payment
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    #[Groups(['read_payment'])]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: ParentEntity::class)]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['read_payment'])]
    private ?ParentEntity $parent = null;

    #[ORM\ManyToOne(targetEntity: Student::class)]
    #[ORM\JoinColumn(nullable: true)]
    #[Groups(['read_payment'])]
    private ?Student $student = null;

    #[ORM\ManyToOne(targetEntity: StudyClass::class, inversedBy: 'payments')]
    #[ORM\JoinColumn(nullable: true)]
    #[Groups(['read_payment'])]
    private ?StudyClass $studyClass = null;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
    #[Groups(['read_payment'])]
    private ?float $amountPaid = null;

    #[ORM\Column(type: 'date')]
    #[Groups(['read_payment'])]
    private ?\DateTimeInterface $paymentDate = null;

    #[ORM\Column(type: 'string', length: 255)]
    #[Groups(['read_payment'])]
    private ?string $paymentType = null;

    #[ORM\Column(type: 'string', length: 255)]
    #[Groups(['read_payment'])]
    private ?string $month = null;

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

    public function getAmountPaid(): ?float
    {
        return $this->amountPaid;
    }

    public function setAmountPaid(float $amountPaid): self
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
}
