<?php

namespace App\Repository;

use App\Entity\SongbookChordsheet;
use App\Entity\Songbook;
use App\Entity\Chordsheet;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<SongbookChordsheet>
 */
class SongbookChordsheetRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SongbookChordsheet::class);
    }

    /**
     * Trouve une relation spécifique entre un songbook et une chordsheet
     */
    public function findBySongbookAndChordsheet(Songbook $songbook, Chordsheet $chordsheet): ?SongbookChordsheet
    {
        return $this->createQueryBuilder('sc')
            ->andWhere('sc.songbook = :songbook')
            ->andWhere('sc.chordsheet = :chordsheet')
            ->setParameter('songbook', $songbook)
            ->setParameter('chordsheet', $chordsheet)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Trouve toutes les chordsheets d'un songbook, ordonnées par position
     */
    public function findChordsheetsBySongbook(Songbook $songbook): array
    {
        return $this->createQueryBuilder('sc')
            ->select('sc', 'c')
            ->join('sc.chordsheet', 'c')
            ->andWhere('sc.songbook = :songbook')
            ->setParameter('songbook', $songbook)
            ->orderBy('sc.position', 'ASC')
            ->addOrderBy('sc.addedAt', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Trouve tous les songbooks qui contiennent une chordsheet spécifique
     */
    public function findSongbooksByChordsheet(Chordsheet $chordsheet): array
    {
        return $this->createQueryBuilder('sc')
            ->select('sc', 's')
            ->join('sc.songbook', 's')
            ->andWhere('sc.chordsheet = :chordsheet')
            ->setParameter('chordsheet', $chordsheet)
            ->orderBy('sc.addedAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Compte le nombre de chordsheets dans un songbook
     */
    public function countChordsheetsBySongbook(Songbook $songbook): int
    {
        return $this->createQueryBuilder('sc')
            ->select('COUNT(sc.id)')
            ->andWhere('sc.songbook = :songbook')
            ->setParameter('songbook', $songbook)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Met à jour les positions des chordsheets dans un songbook
     */
    public function updatePositions(Songbook $songbook, array $chordsheetIds): void
    {
        foreach ($chordsheetIds as $position => $chordsheetId) {
            $this->createQueryBuilder('sc')
                ->update()
                ->set('sc.position', ':position')
                ->andWhere('sc.songbook = :songbook')
                ->andWhere('sc.chordsheet = :chordsheet')
                ->setParameter('position', $position + 1)
                ->setParameter('songbook', $songbook)
                ->setParameter('chordsheet', $chordsheetId)
                ->getQuery()
                ->execute();
        }
    }
}