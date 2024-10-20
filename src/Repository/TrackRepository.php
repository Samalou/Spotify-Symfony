<?php
namespace App\Repository;

use App\Entity\Track;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Track>
 */
class TrackRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Track::class);
    }

    public function findTracksByPopularity(int $popularity): array
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.popularity > :popularity')
            ->setParameter('popularity', $popularity)
            ->orderBy('t.popularity', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
