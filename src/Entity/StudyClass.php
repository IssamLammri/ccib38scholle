<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity]
class StudyClass
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    #[Groups(['read_payment','read_student','read_study_class'])]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 255)]
    #[Groups(['read_payment','read_student','read_study_class','read_session','read_invoice'])]
    private ?string $name = null;

    #[ORM\Column(type: 'integer')]
    #[Groups(['read_study_class','read_session'])]
    private ?int $level = null;

    #[ORM\Column(type: 'string', length: 255)]
    #[Groups(['read_study_class','read_session','read_payment','read_invoice'])]
    private ?string $speciality = null;

    #[ORM\OneToMany(targetEntity: Payment::class, mappedBy: 'studyClass')]
    private Collection $payments;

    public function __construct()
    {
        $this->payments = new ArrayCollection();
    }

    // Getters and Setters

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getLevel(): ?int
    {
        return $this->level;
    }

    public function setLevel(int $level): self
    {
        $this->level = $level;

        return $this;
    }

    public function getSpeciality(): ?string
    {
        return $this->speciality;
    }

    public function setSpeciality(string $speciality): self
    {
        $this->speciality = $speciality;

        return $this;
    }

    /**
     * @return Collection<int, Payment>
     */
    public function getPayments(): Collection
    {
        return $this->payments;
    }

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
            // set the owning side to null (unless already changed)
            if ($payment->getStudyClass() === $this) {
                $payment->setStudyClass(null);
            }
        }

        return $this;
    }

    public function __toString(): string
    {
        return $this->getName().' - '.$this->getSpeciality();
    }

    #[Groups(['read_study_class','read_session'])]
    public function getLevelClass(): string
    {
        return match ($this->level) {
            1 => 'CP',
            2 => 'CE1',
            3 => 'CE2',
            4 => 'CM1',
            5 => 'CM2',
            6 => '6ème',
            7 => '5ème',
            8 => '4ème',
            9 => '3ème',
            10 => '2nde',
            11 => '1ère',
            12 => 'Terminale',
            default => 'Niveau inconnu',
        };
    }
}
