<?php

namespace App\Entity;

use App\Repository\SongbookRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SongbookRepository::class)]
class Songbook
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $title = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $description = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\ManyToOne(inversedBy: 'songbooks')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    /**
     * @var Collection<int, SongbookChordsheet>
     */
    #[ORM\OneToMany(targetEntity: SongbookChordsheet::class, mappedBy: 'songbook', cascade: ['persist', 'remove'])]
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

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

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

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

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
            $songbookChordsheet->setSongbook($this);
        }

        return $this;
    }

    public function removeSongbookChordsheet(SongbookChordsheet $songbookChordsheet): static
    {
        if ($this->songbookChordsheets->removeElement($songbookChordsheet)) {
            // set the owning side to null (unless already changed)
            if ($songbookChordsheet->getSongbook() === $this) {
                $songbookChordsheet->setSongbook(null);
            }
        }

        return $this;
    }

    /**
     * Méthode helper pour obtenir directement les chordsheets
     * @return Collection<int, Chordsheet>
     */
    public function getChordsheets(): Collection
    {
        return $this->songbookChordsheets->map(fn($sc) => $sc->getChordsheet());
    }

    /**
     * Méthode helper pour ajouter une chordsheet avec position optionnelle
     */
    public function addChordsheet(Chordsheet $chordsheet, ?int $position = null): static
    {
        // Vérifier si la relation n'existe pas déjà
        foreach ($this->songbookChordsheets as $songbookChordsheet) {
            if ($songbookChordsheet->getChordsheet() === $chordsheet) {
                return $this; // Relation existe déjà
            }
        }

        $songbookChordsheet = new SongbookChordsheet();
        $songbookChordsheet->setSongbook($this);
        $songbookChordsheet->setChordsheet($chordsheet);
        if ($position !== null) {
            $songbookChordsheet->setPosition($position);
        }

        $this->addSongbookChordsheet($songbookChordsheet);

        return $this;
    }

    /**
     * Méthode helper pour retirer une chordsheet
     */
    public function removeChordsheet(Chordsheet $chordsheet): static
    {
        foreach ($this->songbookChordsheets as $songbookChordsheet) {
            if ($songbookChordsheet->getChordsheet() === $chordsheet) {
                $this->removeSongbookChordsheet($songbookChordsheet);
                break;
            }
        }

        return $this;
    }

    public function __toString(): string
    {
        return $this->title ?? '';
    }
}