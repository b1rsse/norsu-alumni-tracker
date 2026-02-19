<?php

namespace App\Entity;

use App\Repository\AnnouncementRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AnnouncementRepository::class)]
class Announcement
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private string $title;

    #[ORM\Column(type: Types::TEXT)]
    private string $description;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $category = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private \DateTimeInterface $datePosted;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: true)]
    private ?User $postedBy = null;

    #[ORM\Column]
    private bool $isActive = true;

    public function __construct()
    {
        $this->datePosted = new \DateTime();
    }

    // ── Getters & Setters ──

    public function getId(): ?int { return $this->id; }

    public function getTitle(): string { return $this->title; }
    public function setTitle(string $v): static { $this->title = $v; return $this; }

    public function getDescription(): string { return $this->description; }
    public function setDescription(string $v): static { $this->description = $v; return $this; }

    public function getCategory(): ?string { return $this->category; }
    public function setCategory(?string $v): static { $this->category = $v; return $this; }

    public function getDatePosted(): \DateTimeInterface { return $this->datePosted; }
    public function setDatePosted(\DateTimeInterface $v): static { $this->datePosted = $v; return $this; }

    public function getPostedBy(): ?User { return $this->postedBy; }
    public function setPostedBy(?User $v): static { $this->postedBy = $v; return $this; }

    public function isActive(): bool { return $this->isActive; }
    public function setIsActive(bool $v): static { $this->isActive = $v; return $this; }
}
