<?php

namespace App\DataFixtures\ORM;

use App\Entity\Committee;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Ramsey\Uuid\Uuid;

class LoadCommitteeV2Data extends AbstractLoadPostAddressData implements DependentFixtureInterface
{
    public const COMMITTEE_1_UUID = '5e00c264-1d4b-43b8-862e-29edc38389b3';
    public const COMMITTEE_2_UUID = '8c4b48ec-9290-47ae-a5db-d1cf2723e8b3';

    public function load(ObjectManager $manager)
    {
        $manager->persist($object = Committee::createSimple(
            Uuid::fromString(self::COMMITTEE_1_UUID),
            LoadAdherentData::ADHERENT_20_UUID,
            'Comité des 3 communes',
            'Un petit comité avec seulement 3 communes',
        ));

        $object->addZone(LoadGeoZoneData::getZoneReference($manager, 'zone_city_92002'));
        $object->addZone(LoadGeoZoneData::getZoneReference($manager, 'zone_city_92004'));
        $object->addZone(LoadGeoZoneData::getZoneReference($manager, 'zone_city_92007'));

        $manager->persist($object = Committee::createSimple(
            Uuid::fromString(self::COMMITTEE_2_UUID),
            LoadAdherentData::ADHERENT_5_UUID,
            'Second Comité des 3 communes',
            'Un petit comité avec seulement 3 communes',
        ));

        $object->approved('2017-01-27 09:18:33');
        $object->addZone(LoadGeoZoneData::getZoneReference($manager, 'zone_city_92019'));
        $object->addZone(LoadGeoZoneData::getZoneReference($manager, 'zone_city_92014'));
        $object->addZone(LoadGeoZoneData::getZoneReference($manager, 'zone_city_92019'));

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            LoadAdherentData::class,
        ];
    }
}
