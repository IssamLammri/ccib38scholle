<?php

namespace App\Entity;

use App\Repository\ContactRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ContactRepository::class)]
#[ORM\Table(
    name: 'contact',
    indexes: [
        new ORM\Index(name: 'contact_status_idx', columns: ['status']),
        new ORM\Index(name: 'contact_locale_idx', columns: ['locale'])
    ]
)]
#[UniqueEntity(fields: ['email'], message: 'Cet email est déjà enregistré.')]
#[ORM\HasLifecycleCallbacks]
class Contact
{
    public const STATUS_SUBSCRIBED   = 'subscribed';
    public const STATUS_UNSUBSCRIBED = 'unsubscribed';
    public const STATUS_BOUNCED      = 'bounced';
    public const STATUS_COMPLAINED   = 'complained';
    public const STATUS_BLOCKED      = 'blocked';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    #[Groups(['contact:read'])]
    private ?int $id = null;

    // Pour liens de gestion (désabonnement, confirmation…)
    #[ORM\Column(length: 64, unique: true)]
    private string $publicToken = '';

    #[ORM\Column(length: 150)]
    #[Assert\NotBlank(message: 'L’email est obligatoire.')]
    #[Assert\Email(mode: Assert\Email::VALIDATION_MODE_HTML5)]
    #[Groups(['contact:read'])]
    private string $email = '';

    #[ORM\Column(length: 100, nullable: true)]
    #[Groups(['contact:read'])]
    private ?string $firstName = null;

    #[ORM\Column(length: 100, nullable: true)]
    #[Groups(['contact:read'])]
    private ?string $lastName = null;

    #[ORM\Column(length: 30, nullable: true)]
    private ?string $phone = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $address = null;

    #[ORM\Column(length: 12, nullable: true)]
    private ?string $postalCode = null;

    #[ORM\Column(length: 150, nullable: true)]
    private ?string $city = null;

    #[ORM\Column(length: 8, options: ['default' => 'fr'])]
    private string $locale = 'fr';

    // Étiquettes libres (ex: "parents", "benevoles", "atelier-informatique")
    #[ORM\Column(type: 'json')]
    private array $tags = [];

    // Segments métiers (ex: "newsletter", "infos-centre", "urgence")
    #[ORM\Column(type: 'json')]
    private array $segments = [];

    // Consentement newsletter (opt-in)
    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private bool $consentNewsletter = false;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $consentAt = null;

    #[ORM\Column(length: 45, nullable: true)]
    private ?string $consentIp = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $consentSource = null; // ex: "formulaire site", "import CSV"

    #[ORM\Column(length: 20)]
    #[Assert\Choice(choices: [
        self::STATUS_SUBSCRIBED,
        self::STATUS_UNSUBSCRIBED,
        self::STATUS_BOUNCED,
        self::STATUS_COMPLAINED,
        self::STATUS_BLOCKED
    ])]
    private string $status = self::STATUS_SUBSCRIBED;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $unsubscribedAt = null;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $lastEmailedAt = null;

    public function __construct()
    {
        $this->createdAt   = new \DateTimeImmutable();
        $this->publicToken = bin2hex(random_bytes(16));
    }

    #[ORM\PreUpdate]
    public function touch(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }

    // -------- GETTERS / SETTERS --------

    public function getId(): ?int { return $this->id; }

    public function getPublicToken(): string { return $this->publicToken; }
    public function setPublicToken(string $token): self { $this->publicToken = $token; return $this; }

    public function getEmail(): string { return $this->email; }
    public function setEmail(string $email): self
    {
        $this->email = mb_strtolower(trim($email));
        return $this;
    }

    public function getFirstName(): ?string { return $this->firstName; }
    public function setFirstName(?string $firstName): self { $this->firstName = $firstName; return $this; }

    public function getLastName(): ?string { return $this->lastName; }
    public function setLastName(?string $lastName): self { $this->lastName = $lastName; return $this; }

    public function getPhone(): ?string { return $this->phone; }
    public function setPhone(?string $phone): self { $this->phone = $phone; return $this; }

    public function getAddress(): ?string { return $this->address; }
    public function setAddress(?string $address): self { $this->address = $address; return $this; }

    public function getPostalCode(): ?string { return $this->postalCode; }
    public function setPostalCode(?string $postalCode): self { $this->postalCode = $postalCode; return $this; }

    public function getCity(): ?string { return $this->city; }
    public function setCity(?string $city): self { $this->city = $city; return $this; }

    public function getLocale(): string { return $this->locale; }
    public function setLocale(string $locale): self { $this->locale = $locale; return $this; }

    public function getTags(): array { return $this->tags; }
    public function setTags(array $tags): self { $this->tags = array_values(array_unique($tags)); return $this; }

    public function getSegments(): array { return $this->segments; }
    public function setSegments(array $segments): self { $this->segments = array_values(array_unique($segments)); return $this; }

    public function hasTag(string $tag): bool { return \in_array($tag, $this->tags, true); }
    public function addTag(string $tag): self
    {
        if (!$this->hasTag($tag)) $this->tags[] = $tag;
        return $this;
    }
    public function removeTag(string $tag): self
    {
        $this->tags = array_values(array_filter($this->tags, fn($t) => $t !== $tag));
        return $this;
    }

    public function isConsentNewsletter(): bool { return $this->consentNewsletter; }
    public function setConsentNewsletter(bool $consent): self
    {
        $this->consentNewsletter = $consent;
        if ($consent && !$this->consentAt) $this->consentAt = new \DateTimeImmutable();
        return $this;
    }

    public function getConsentAt(): ?\DateTimeImmutable { return $this->consentAt; }
    public function setConsentAt(?\DateTimeImmutable $dt): self { $this->consentAt = $dt; return $this; }

    public function getConsentIp(): ?string { return $this->consentIp; }
    public function setConsentIp(?string $ip): self { $this->consentIp = $ip; return $this; }

    public function getConsentSource(): ?string { return $this->consentSource; }
    public function setConsentSource(?string $src): self { $this->consentSource = $src; return $this; }

    public function getStatus(): string { return $this->status; }
    public function setStatus(string $status): self { $this->status = $status; return $this; }

    public function getCreatedAt(): \DateTimeImmutable { return $this->createdAt; }
    public function setCreatedAt(\DateTimeImmutable $dt): self { $this->createdAt = $dt; return $this; }

    public function getUpdatedAt(): ?\DateTimeImmutable { return $this->updatedAt; }
    public function setUpdatedAt(?\DateTimeImmutable $dt): self { $this->updatedAt = $dt; return $this; }

    public function getUnsubscribedAt(): ?\DateTimeImmutable { return $this->unsubscribedAt; }
    public function setUnsubscribedAt(?\DateTimeImmutable $dt): self { $this->unsubscribedAt = $dt; return $this; }

    public function getLastEmailedAt(): ?\DateTimeImmutable { return $this->lastEmailedAt; }
    public function setLastEmailedAt(?\DateTimeImmutable $dt): self { $this->lastEmailedAt = $dt; return $this; }

    // Helpers
    public function getFullName(): string
    {
        return trim(($this->firstName ?? '') . ' ' . ($this->lastName ?? ''));
    }

    public function unsubscribe(): self
    {
        $this->status = self::STATUS_UNSUBSCRIBED;
        $this->unsubscribedAt = new \DateTimeImmutable();
        $this->consentNewsletter = false;
        return $this;
    }
}
