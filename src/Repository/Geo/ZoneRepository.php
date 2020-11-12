<?php

namespace App\Repository\Geo;

use App\Entity\Geo\Canton;
use App\Entity\Geo\City;
use App\Entity\Geo\CityCommunity;
use App\Entity\Geo\ConsularDistrict;
use App\Entity\Geo\Country;
use App\Entity\Geo\Department;
use App\Entity\Geo\District;
use App\Entity\Geo\ForeignDistrict;
use App\Entity\Geo\Region;
use App\Entity\Geo\Zone;
use App\Entity\Geo\ZoneableInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bridge\Doctrine\RegistryInterface;

final class ZoneRepository extends ServiceEntityRepository
{
    private const TYPES = [
        Country::class => Zone::COUNTRY,
        Region::class => Zone::REGION,
        Department::class => Zone::DEPARTMENT,
        District::class => Zone::DISTRICT,
        Canton::class => Zone::CANTON,
        CityCommunity::class => Zone::CITY_COMMUNITY,
        City::class => Zone::CITY,
        ForeignDistrict::class => Zone::FOREIGN_DISTRICT,
        ConsularDistrict::class => Zone::CONSULAR_DISTRICT,
    ];

    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Zone::class);
    }

    public function zoneableAsZone(ZoneableInterface $zoneable): Zone
    {
        $zone = $this->findOneBy([
            'code' => $zoneable->getCode(),
            'type' => $zoneable->getZoneType(),
        ]);

        if (!$zone) {
            $zone = new Zone($zoneable->getZoneType(), $zoneable->getCode(), $zoneable->getName());
        }

        $zone->activate($zoneable->isActive());
        $zone->setName($zoneable->getName());

        return $zone;
    }

    public function createSelectForCandidatesQueryBuilder(): QueryBuilder
    {
        return $this->createQueryBuilder('zone')
            ->where('zone.type IN (:types)')
            ->setParameters([
                'types' => Zone::CANDIDATE_TYPES,
            ])
        ;
    }

    /**
     * @return Zone[]
     */
    public function searchByTermAndManagedZones(string $term, array $zones, int $max): array
    {
        $qb = $this
            ->createQueryBuilder('zone')
            ->leftJoin('zone.parents', 'parents')
        ;

        if ('' === $term) {
            return [];
        }

        if ($zones) {
            $qb
                ->andWhere(
                    $qb->expr()->orX(
                        $qb->expr()->in('zone.id', ':zones'),
                        $qb->expr()->in('parents.id', ':zones'),
                    )
                )
                ->setParameter(':zones', $zones)
                ->orderBy('zone.name')
            ;
        }

        if ('' !== $term) {
            $qb
                ->andWhere(
                    $qb->expr()->orX(
                        $qb->expr()->like('zone.name', ':term'),
                        $qb->expr()->like('zone.code', ':term'),
                    )
                )
                ->setParameter(':term', "%$term%")
            ;
        }

        return $qb
            ->setMaxResults($max)
            ->getQuery()
            ->getResult()
        ;
    }

    public function findForMandateAdminAutocomplete(?string $term, array $types, int $limit): array
    {
        if (!$term || !$types) {
            return [];
        }

        $qb = $this->createQueryBuilder('zone');

        return $qb
            ->andWhere(
                $qb->expr()->orX(
                    $qb->expr()->like('zone.name', ':term'),
                    $qb->expr()->like('zone.code', ':term'),
                )
            )
            ->andWhere($qb->expr()->in('zone.type', ':types'))
            ->setParameter(':term', "$term%")
            ->setParameter(':types', $types)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult()
        ;
    }
}
