<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_USERNAME', fields: ['username'])]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email'])]
#[UniqueEntity(fields: ['username'], message: 'Ce nom d\'utilisateur est déjà pris.')]
#[UniqueEntity(fields: ['email'], message: 'Cet email est déjà utilisé.')]

class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private ?int $id = null;

    #[ORM\Column(type: "string", length: 255, unique: true)]
    private ?string $username = null;

    #[ORM\Column(type: "string", length: 255, unique : true)]
    private ?string $email = null;

    #[ORM\Column(type: "string", length: 255)]
    private ?string $password = null;

    #[ORM\Column(type: "datetime_immutable")]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(type: "datetime", nullable: true)]
    private ?\DateTimeInterface $lastConnection = null;

    #[ORM\ManyToOne(inversedBy: 'users')]
    #[ORM\JoinColumn(nullable: false)]
    private Role $role;

    #[ORM\Column(type: "string", length: 255, nullable:true)]
    private ?string $avatarUrl = null;

    #[ORM\Column(type:"boolean")]
    private ?bool $active = null;

    // ⚠️ This field is not persisted to the database.
    private ?string $plainPassword = null;
    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->active = true;
    }

    #[ORM\Column(type: 'boolean')]
    private $isBanned = false;  // Par défaut, l'utilisateur n'est pas banni

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $termsAcceptedAt = null;

    // Getters

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUsername(): string
    {
        return (string) $this->username;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getLastConnection() : ?\DateTimeInterface
    {
        return $this->lastConnection;
    }

    public function getRole(): Role
    {
        return $this->role;
    }

    public function getAvatarUrl(): ?string
    {
        return $this->avatarUrl;
    }

    public function getPlainPassword(): ?string
    {
        return $this->plainPassword;
    }

    public function isActive(): ?bool
    {
        return $this->active;
    }

    public function getIsBanned(): ?bool
    {
        return $this->isBanned;
    }

    public function getTermsAcceptedAt(): ?\DateTimeInterface
    {
        return $this->termsAcceptedAt;
    }

    //Setters

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    public function setUsername(string $username): static
    {
        $this->username = $username;
        return $this;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): static
    {
        $this->createdAt= $createdAt;
        return $this;
    }
    public function setLastConnection(?\DateTimeInterface $lastConnection): static
    {
        $this->lastConnection = $lastConnection;
        return $this;
    }

    public function setRole(Role $role): static
    {
        $this->role = $role;
        return $this;
    }

    public function setAvatarUrl(?string $avatarUrl): static
    {
        $this->avatarUrl = $avatarUrl;
        return $this;
    }

    public function setActive(bool $active): static
    {
        $this->active = $active;
        return $this;
    }

    public function setPlainPassword(?string $plainPassword): self
    {
        $this->plainPassword = $plainPassword;
        return $this;
    }

    public function setIsBanned(bool $isBanned): self
    {
        $this->isBanned = $isBanned;

        return $this;
    }

    public function setTermsAcceptedAt(?\DateTimeInterface $termsAcceptedAt): self
    {
        $this->termsAcceptedAt = $termsAcceptedAt;
        return $this;
    }

    // UserInterface Methods / PasswordAuthenticatedUserInterface

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;

        $this->plainPassword = null;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = [$this->role->getName()];

        //guarantee every user at least has ROLE_USER
        $roles[] = "ROLE_USER";

        return array_unique($roles);
    }
    public function anonymize(string $hashedPassword): self
    {
        $randomSuffix = bin2hex(random_bytes(8));

        $this->setUsername('deleted_user_' . $randomSuffix);
        $this->setEmail('deleted_' . $randomSuffix . '@example.com');
        $this->setPassword($hashedPassword);
        $this->setAvatarUrl(null);
        $this->setActive(false);
        $this->setIsBanned(true);
        $this->setLastConnection(null);
        $this->setPlainPassword(null);

        return $this;
    }
}
