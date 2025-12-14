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
    #[Groups(['student_session_read','presence_session'])]
    private ?int $id = null;

    #[ORM\Column(type: 'datetime_immutable')]
    #[Groups(['student_session_read','presence_session'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\ManyToOne(targetEntity: Student::class, inversedBy: 'presences')]
    #[ORM\JoinColumn(name: 'student_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    #[Groups(['student_session_read','presence_session'])]
    private ?Student $student = null;

    #[ORM\ManyToOne(targetEntity: Session::class, inversedBy: 'presences')]
    #[ORM\JoinColumn(name: 'session_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    #[Groups(['presence_session'])]
    private ?Session $session = null;

    #[ORM\Column(type: 'boolean', nullable: true, options: ['default' => null])]
    #[Groups(['student_session_read','presence_session'])]
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
