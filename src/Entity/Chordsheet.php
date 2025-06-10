<?php

namespace App\Entity;

use App\Repository\ChordsheetRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ChordsheetRepository::class)]
class Chordsheet
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'chordsheets')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\Column(length: 255, nullable: false)]
    #[Assert\NotBlank(message: 'Le titre ne peut pas être vide.')]
    private string $title;

    #[ORM\Column(type: Types::TEXT, nullable: false)]
    #[Assert\NotBlank(message: 'Le contenu ne peut pas être vide.')]
    private string $content;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(type: 'boolean')]
    private bool $isPublic = false;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $publishedAt = null;

    /**
     * @var Collection<int, SongbookChordsheet>
     */
    #[ORM\OneToMany(targetEntity: SongbookChordsheet::class, mappedBy: 'chordsheet', cascade: ['persist', 'remove'])]
    private Collection $songbookChordsheets;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->songbookChordsheets = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function setContent(string $content): static
    {
        $this->content = $content;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function isPublic(): bool
    {
        return $this->isPublic;
    }

    public function setIsPublic(bool $isPublic): static
    {
        $this->isPublic = $isPublic;

        return $this;
    }

    public function getPublishedAt(): ?\DateTimeImmutable
    {
        return $this->publishedAt;
    }

    public function setPublishedAt(?\DateTimeImmutable $publishedAt): static
    {
        $this->publishedAt = $publishedAt;

        return $this;
    }

    /**
     * @return Collection<int, SongbookChordsheet>
     */
    public function getSongbookChordsheets(): Collection
    {
        return $this->songbookChordsheets;
    }

    public function addSongbookChordsheet(SongbookChordsheet $songbookChordsheet): static
    {
        if (!$this->songbookChordsheets->contains($songbookChordsheet)) {
            $this->songbookChordsheets->add($songbookChordsheet);
            $songbookChordsheet->setChordsheet($this);
        }

        return $this;
    }

    public function removeSongbookChordsheet(SongbookChordsheet $songbookChordsheet): static
    {
        if ($this->songbookChordsheets->removeElement($songbookChordsheet)) {
            // set the owning side to null (unless already changed)
            if ($songbookChordsheet->getChordsheet() === $this) {
                $songbookChordsheet->setChordsheet(null);
            }
        }

        return $this;
    }

    /**
     * Méthode helper pour obtenir directement les songbooks
     * @return Collection<int, Songbook>
     */
    public function getSongbooks(): Collection
    {
        return $this->songbookChordsheets->map(fn($sc) => $sc->getSongbook());
    }

    public function __toString(): string
    {
        return $this->title ?? '';
    }
}