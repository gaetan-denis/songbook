<?php

namespace App\Repository;

use App\Entity\Chordsheet;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Chordsheet>
 */
class ChordsheetRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Chordsheet::class);
    }

    /**
     * Trouve les partitions disponibles pour un utilisateur
     * (ses propres partitions + les partitions publiques)
     */
    public function findAvailableForUser($user): array
    {
        return $this->createQueryBuilder('c')
            ->where('c.user = :user OR c.isPublic = true')
            ->setParameter('user', $user)
            ->orderBy('c.title', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Trouve les partitions disponibles pour un utilisateur avec recherche
     */
    public function findAvailableForUserWithSearch($user, string $search = null): array
    {
        $qb = $this->createQueryBuilder('c')
            ->where('c.user = :user OR c.isPublic = true')
            ->setParameter('user', $user);

        if ($search) {
            $qb->andWhere('c.title LIKE :search OR c.content LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }

        return $qb->orderBy('c.title', 'ASC')
            ->getQuery()
            ->getResult();
    }
}