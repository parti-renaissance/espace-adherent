<?php

namespace App\Repository\Geo;

use App\Entity\Geo\Zone;
use App\Entity\Geo\ZoneableInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

final class ZoneRepository extends ServiceEntityRepository
{
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
}
