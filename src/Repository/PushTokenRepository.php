<?php

namespace App\Repository;

use App\Entity\Adherent;
use App\Entity\Device;
use App\Entity\PushToken;
use Doctrine\ORM\Query\Expr\Orx;
use Doctrine\Persistence\ManagerRegistry;

class PushTokenRepository extends EventRepository
{
    use GeoZoneTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PushToken::class);
    }

    public function findIdentifiersForZones(array $zones): array
    {
        $qb = $this->createQueryBuilder('token')
            ->select('DISTINCT(token.identifier)')
            ->leftJoin('token.adherent', 'adherent')
            ->leftJoin('token.device', 'device')
        ;

        $adherentZonesCondition = $this->createGeoZonesQueryBuilder(
            $zones,
            $qb,
            Adherent::class,
            'adherent_2',
            'zones',
            'adherent_zone_2',
            null,
            true,
            'adherent_zone_parent'
        );

        $deviceZoneCondition = $this->createGeoZonesQueryBuilder(
            $zones,
            $qb,
            Device::class,
            'device_2',
            'zones',
            'device_zone_2',
            null,
            true,
            'device_zone_parent'
        );

        $tokens = $qb
            ->andWhere((new Orx())
                ->add(sprintf('adherent.id IN (%s)', $adherentZonesCondition->getDQL()))
                ->add(sprintf('device.id IN (%s)', $deviceZoneCondition->getDQL()))
            )
            ->getQuery()
            ->getArrayResult()
        ;

        return array_map('current', $tokens);
    }
}
