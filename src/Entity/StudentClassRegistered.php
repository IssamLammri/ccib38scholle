<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity]
class StudentClassRegistered
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    #[Groups(['read_student_class_registered'])]
    private ?int $id = null;

    #[ORM\Column(type: 'datetime_immutable')]
    #[Groups(['read_student_class_registered'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\ManyToOne(targetEntity: Student::class, inversedBy: 'classRegistrations')]
    #[ORM\JoinColumn(name: 'student_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    #[Groups(['read_student_class_registered'])]
    private ?Student $student = null; // Relation Many-to-One avec Student

    #[ORM\ManyToOne(targetEntity: StudyClass::class)]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['read_payment','read_student'])]
    private ?StudyClass $studyClass = null; // Relation Many-to-One avec StudyClass

    #[ORM\Column(type: 'boolean', nullable: true, options: ['default' => null])]
    #[Groups(['read_student_class_registered','read_payment'])]
    private ?bool $active = null;

    public function __construct(StudyClass $studyClass, Student $student)
    {
        $this->studyClass = $studyClass;
        $this->student = $student;
        $this->active = true;
        $this->createdAt = new \DateTimeImmutable();
    }

    // Getters et Setters

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): self
    {
        $this->createdAt = $createdAt;

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

    public function isActive(): ?bool
    {
        return $this->active;
    }

    public function setActive(?bool $active): self
    {
        $this->active = $active;

        return $this;
    }
}
