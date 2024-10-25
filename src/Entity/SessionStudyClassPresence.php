<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity]
class SessionStudyClassPresence
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    #[Groups(['student_session_read'])]
    private ?int $id = null;

    #[ORM\Column(type: 'datetime_immutable')]
    #[Groups(['student_session_read'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\ManyToOne(targetEntity: Student::class)]
    #[ORM\JoinColumn(nullable: true)]
    #[Groups(['student_session_read'])]
    private ?Student $student = null;

    #[ORM\ManyToOne(targetEntity: Session::class)]
    #[ORM\JoinColumn(nullable: true)]
    private ?Session $session = null;

    #[ORM\Column(type: 'boolean')]
    #[ORM\JoinColumn(nullable: true)]
    #[Groups(['student_session_read'])]
    private ?bool $isPresent = null;

    // create a constructor with the required fields
    public function __construct(Student $student, Session $session)
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->session = $session;
        $this->student = $student;
    }

    // Getters and Setters

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(?\DateTimeImmutable $createdAt): SessionStudyClassPresence
    {
        $this->createdAt = $createdAt;
        return $this;
    }


    public function getStudent(): ?Student
    {
        return $this->student;
    }

    public function setStudent(?Student $student): SessionStudyClassPresence
    {
        $this->student = $student;
        return $this;
    }

    public function getIsPresent(): ?bool
    {
        return $this->isPresent;
    }

    public function setIsPresent(?bool $isPresent): SessionStudyClassPresence
    {
        $this->isPresent = $isPresent;
        return $this;
    }

    public function getSession(): ?Session
    {
        return $this->session;
    }

    public function setSession(?Session $session): SessionStudyClassPresence
    {
        $this->session = $session;
        return $this;
    }
}
