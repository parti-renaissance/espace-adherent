<?php

namespace App\DataFixtures\ORM;

use App\Entity\Geo\Borough;
use App\Entity\Geo\Canton;
use App\Entity\Geo\City;
use App\Entity\Geo\CityCommunity;
use App\Entity\Geo\ConsularDistrict;
use App\Entity\Geo\Country;
use App\Entity\Geo\CustomZone;
use App\Entity\Geo\Department;
use App\Entity\Geo\District;
use App\Entity\Geo\ForeignDistrict;
use App\Entity\Geo\Region;
use App\Entity\Geo\Zone;
use App\Entity\Geo\ZoneableInterface;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class LoadGeoZoneData extends Fixture
{
    private const ZONEABLES = [
        Country::class,
        Region::class,
        Department::class,
        District::class,
        Canton::class,
        CityCommunity::class,
        City::class,
        Borough::class,
        CustomZone::class,
        ForeignDistrict::class,
        ConsularDistrict::class,
    ];

    public function load(ObjectManager $manager): void
    {
        foreach (self::ZONEABLES as $class) {
            $zoneables = $manager->getRepository($class)->findAll();
            foreach ($zoneables as $zoneable) {
                $this->persistAsZone($manager, $zoneable);
            }
        }

        $manager->flush();
    }

    private function persistAsZone(ObjectManager $manager, ZoneableInterface $zoneable): Zone
    {
        $reference = sprintf('zone_%s_%s', $zoneable->getZoneType(), $zoneable->getCode());

        if (!$this->hasReference($reference)) {
            $repository = $manager->getRepository(Zone::class);
            $zone = $repository->zoneableAsZone($zoneable);

            $zone->clearParents();
            foreach ($zoneable->getParents() as $zoneableParent) {
                $zoneParent = $this->persistAsZone($manager, $zoneableParent);
                $zone->addParent($zoneParent);
            }

            $manager->persist($zone);
            $this->addReference($reference, $zone);
        }

        /* @var Zone $zone */
        $zone = $this->getReference($reference);

        return $zone;
    }

    public function getDependencies(): array
    {
        return [
            LoadGeoData::class,
        ];
    }
}
