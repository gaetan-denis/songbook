<?php

namespace App\Repository;

use App\Entity\Songbook;
use App\Entity\SongbookChordsheet;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Songbook>
 */
class SongbookRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Songbook::class);
    }

    //    /**
    //     * @return Songbook[] Returns an array of Songbook objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('s')
    //            ->andWhere('s.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('s.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Songbook
    //    {
    //        return $this->createQueryBuilder('s')
    //            ->andWhere('s.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
    /**
     * Récupère les relations SongbookChordsheet d'un songbook triées par position
     */
    public function getSongbookChordsheetsOrdered(Songbook $songbook): array
    {
        return $this->getEntityManager()
            ->getRepository(SongbookChordsheet::class)
            ->createQueryBuilder('sc')
            ->leftJoin('sc.chordsheet', 'c')
            ->addSelect('c')
            ->where('sc.songbook = :songbook')
            ->setParameter('songbook', $songbook)
            ->orderBy('sc.position', 'ASC')
            ->addOrderBy('sc.addedAt', 'ASC') // Fallback si position est nulle
            ->getQuery()
            ->getResult();
    }
}
