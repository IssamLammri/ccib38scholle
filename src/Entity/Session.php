<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity]
class Session
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    #[Groups(['read_session'])]
    private ?int $id = null;

    #[ORM\Column(type: 'datetime_immutable')]
    #[Groups(['read_session'])]
    private ?\DateTimeImmutable $startTime = null;

    #[ORM\Column(type: 'datetime_immutable')]
    #[Groups(['read_session'])]
    private ?\DateTimeImmutable $endTime = null;

    #[ORM\ManyToOne(targetEntity: Room::class)]
    #[ORM\JoinColumn(name: 'room_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    #[Groups(['read_session'])]
    private ?Room $room = null;

    #[ORM\ManyToOne(targetEntity: StudyClass::class)]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['read_session'])]
    private ?StudyClass $studyClass = null;

    #[ORM\ManyToOne(targetEntity: Teacher::class)]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['read_session'])]
    private ?Teacher $teacher = null;

    // Getters and Setters

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStartTime(): ?\DateTimeImmutable
    {
        return $this->startTime;
    }

    public function setStartTime(\DateTimeImmutable $startTime): self
    {
        $this->startTime = $startTime;

        return $this;
    }

    public function getEndTime(): ?\DateTimeImmutable
    {
        return $this->endTime;
    }

    public function setEndTime(\DateTimeImmutable $endTime): self
    {
        $this->endTime = $endTime;

        return $this;
    }

    public function getRoom(): ?Room
    {
        return $this->room;
    }

    public function setRoom(Room $room): self
    {
        $this->room = $room;

        return $this;
    }

    public function getStudyClass(): ?StudyClass
    {
        return $this->studyClass;
    }

    public function setStudyClass(StudyClass $studyClass): self
    {
        $this->studyClass = $studyClass;

        return $this;
    }

    public function getTeacher(): ?Teacher
    {
        return $this->teacher;
    }

    public function setTeacher(Teacher $teacher): self
    {
        $this->teacher = $teacher;

        return $this;
    }
}
