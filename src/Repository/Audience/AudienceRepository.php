<?php

declare(strict_types=1);

namespace App\Repository\Audience;

use App\Entity\Audience\Audience;
use App\Entity\Geo\Zone;
use App\Repository\GeoZoneTrait;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends \Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository<\App\Entity\Audience\Audience>
 */
class AudienceRepository extends ServiceEntityRepository
{
    use GeoZoneTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Audience::class);
    }

    /**
     * @param Zone[] $zones
     *
     * @return Audience[]
     */
    public function findByZones(string $scope, array $zones): array
    {
        $qb = $this->createQueryBuilder('audience')
            ->where('audience.scope = :scope')
            ->setParameter('scope', $scope)
        ;

        return $this
            ->withGeoZones(
                $zones,
                $qb,
                'audience',
                Audience::class,
                'a2',
                'zones',
                'z2'
            )
            ->getQuery()
            ->getResult()
        ;
    }
}
