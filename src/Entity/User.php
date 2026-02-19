<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email'])]
#[UniqueEntity(fields: ['email'], message: 'There is already an account with this email')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180, unique: true)]
    private ?string $email = null;

    #[ORM\Column(length: 255)]
    private ?string $firstName = null;

    #[ORM\Column(length: 255)]
    private ?string $lastName = null;

    /** @var list<string> */
    #[ORM\Column]
    private array $roles = [];

    #[ORM\Column]
    private ?string $password = null;

    #[ORM\Column(length: 50)]
    private string $accountStatus = 'pending'; // pending, active, inactive

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private \DateTimeInterface $dateRegistered;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $lastLogin = null;

    #[ORM\OneToOne(mappedBy: 'user', targetEntity: Alumni::class, cascade: ['persist'])]
    private ?Alumni $alumni = null;

    public function __construct()
    {
        $this->dateRegistered = new \DateTime();
    }

    public function getId(): ?int { return $this->id; }

    public function getEmail(): ?string { return $this->email; }
    public function setEmail(string $email): static { $this->email = $email; return $this; }

    public function getFirstName(): ?string { return $this->firstName; }
    public function setFirstName(string $firstName): static { $this->firstName = $firstName; return $this; }

    public function getLastName(): ?string { return $this->lastName; }
    public function setLastName(string $lastName): static { $this->lastName = $lastName; return $this; }

    public function getUserIdentifier(): string { return (string) $this->email; }

    /** @return list<string> */
    public function getRoles(): array
    {
        $roles = $this->roles;
        $roles[] = 'ROLE_USER';
        return array_unique($roles);
    }

    /** @param list<string> $roles */
    public function setRoles(array $roles): static { $this->roles = $roles; return $this; }

    public function getPassword(): ?string { return $this->password; }
    public function setPassword(string $password): static { $this->password = $password; return $this; }

    public function eraseCredentials(): void {}

    public function getAccountStatus(): string { return $this->accountStatus; }
    public function setAccountStatus(string $v): static { $this->accountStatus = $v; return $this; }

    public function getDateRegistered(): \DateTimeInterface { return $this->dateRegistered; }
    public function setDateRegistered(\DateTimeInterface $v): static { $this->dateRegistered = $v; return $this; }

    public function getLastLogin(): ?\DateTimeInterface { return $this->lastLogin; }
    public function setLastLogin(?\DateTimeInterface $v): static { $this->lastLogin = $v; return $this; }

    public function getAlumni(): ?Alumni { return $this->alumni; }
    public function setAlumni(?Alumni $alumni): static { $this->alumni = $alumni; return $this; }

    public function getFullName(): string { return $this->firstName . ' ' . $this->lastName; }

    public function isAdmin(): bool { return in_array('ROLE_ADMIN', $this->roles); }
}
