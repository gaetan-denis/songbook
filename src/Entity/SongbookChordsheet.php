<?php

namespace App\Entity;

use App\Repository\SongbookChordsheetRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SongbookChordsheetRepository::class)]
#[ORM\Table(name: 'songbook_chordsheet')]
// ❌ SUPPRIMÉ : #[ORM\UniqueConstraint(name: 'UNIQ_SONGBOOK_CHORDSHEET', fields: ['songbook', 'chordsheet'])]
class SongbookChordsheet
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Songbook::class, inversedBy: 'songbookChordsheets')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Songbook $songbook = null;

    #[ORM\ManyToOne(targetEntity: Chordsheet::class, inversedBy: 'songbookChordsheets')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Chordsheet $chordsheet = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $addedAt = null;

    #[ORM\Column(nullable: true)]
    private ?int $position = null;

    public function __construct()
    {
        $this->addedAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSongbook(): ?Songbook
    {
        return $this->songbook;
    }

    public function setSongbook(?Songbook $songbook): static
    {
        $this->songbook = $songbook;

        return $this;
    }

    public function getChordsheet(): ?Chordsheet
    {
        return $this->chordsheet;
    }

    public function setChordsheet(?Chordsheet $chordsheet): static
    {
        $this->chordsheet = $chordsheet;

        return $this;
    }

    public function getAddedAt(): ?\DateTimeImmutable
    {
        return $this->addedAt;
    }

    public function setAddedAt(\DateTimeImmutable $addedAt): static
    {
        $this->addedAt = $addedAt;

        return $this;
    }

    public function getPosition(): ?int
    {
        return $this->position;
    }

    public function setPosition(?int $position): static
    {
        $this->position = $position;

        return $this;
    }
}