<?php

namespace App\Entity;

use App\Repository\BookRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: BookRepository::class)]
#[ORM\Table(name: 'book')]
class Book
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['read_book', 'read_payment','read_invoice'])]
    private ?int $id = null;

    // Nom du livre
    #[ORM\Column(length: 180)]
    #[Assert\NotBlank(message: 'Le nom du livre est obligatoire.')]
    #[Assert\Length(max: 180)]
    #[Groups(['read_book', 'read_payment','read_invoice'])]
    private ?string $name = null;

    // Description
    #[ORM\Column(type: 'text', nullable: true)]
    #[Groups(['read_book'])]
    private ?string $description = null;

    // Nom de fichier de l'image (ex: "harry-potter.jpg")
    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['read_book'])]
    private ?string $imageName = null;

    // Prix de vente (TTC)
    #[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
    #[Assert\NotBlank(message: 'Le prix de vente est obligatoire.')]
    #[Assert\PositiveOrZero(message: 'Le prix de vente doit être positif.')]
    #[Groups(['read_book', 'read_payment','read_invoice'])]
    private ?string $salePrice = null;

    // Prix d’achat (pour ta marge)
    #[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
    #[Assert\NotBlank(message: 'Le prix d’achat est obligatoire.')]
    #[Assert\PositiveOrZero(message: 'Le prix d’achat doit être positif.')]
    #[Groups(['read_book'])]
    private ?string $purchasePrice = null;

    // Niveau scolaire
    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\NotNull(message: 'Le niveau scolaire est obligatoire.')]
    #[Groups(['read_book','read_invoice'])]
    private ?string $level = null;

    public function getId(): ?int
    {
        return $this->id ?? null;
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

    public function getDescription(): ?string
    {
        return $this->description;
    }
    public function setDescription(?string $description): self
    {
        $this->description = $description;
        return $this;
    }

    public function getImageName(): ?string
    {
        return $this->imageName;
    }
    public function setImageName(?string $imageName): self
    {
        $this->imageName = $imageName;
        return $this;
    }

    public function getSalePrice(): ?string
    {
        return $this->salePrice;
    }
    public function setSalePrice(string $salePrice): self
    {
        $this->salePrice = $salePrice;
        return $this;
    }

    public function getPurchasePrice(): ?string
    {
        return $this->purchasePrice;
    }
    public function setPurchasePrice(string $purchasePrice): self
    {
        $this->purchasePrice = $purchasePrice;
        return $this;
    }

    public function getLevel(): ?string
    {
        return $this->level;
    }
    public function setLevel(string $level): self
    {
        $this->level = $level;
        return $this;
    }
}
