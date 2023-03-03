<?php

namespace App\DataFixtures\ORM;

use App\Entity\Committee;
use App\Entity\CommitteeElection;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Ramsey\Uuid\Uuid;

class LoadCommitteeV2Data extends AbstractLoadPostAddressData implements DependentFixtureInterface
{
    public const COMMITTEE_1_UUID = '5e00c264-1d4b-43b8-862e-29edc38389b3';
    public const COMMITTEE_2_UUID = '8c4b48ec-9290-47ae-a5db-d1cf2723e8b3';

    public const COMMITTEE_ELECTION_1_UUID = '278fcb58-53b4-4798-a3be-e5bb92f7f0f2';
    public const COMMITTEE_ELECTION_2_UUID = 'f86ee969-5eca-4666-bcd4-7f7388372e0b';

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
        $object->addZone(LoadGeoZoneData::getZoneReference($manager, 'zone_city_92012'));
        $object->addZone(LoadGeoZoneData::getZoneReference($manager, 'zone_city_92014'));
        $object->addZone(LoadGeoZoneData::getZoneReference($manager, 'zone_city_92019'));

        $object->setCurrentElection(new CommitteeElection($this->getReference('designation-committee-02'), Uuid::fromString(self::COMMITTEE_ELECTION_1_UUID)));
        $object->addElection(new CommitteeElection($this->getReference('designation-committee-03'), Uuid::fromString(self::COMMITTEE_ELECTION_2_UUID)));

        $adherent5 = $this->getReference('adherent-5');
        $manager->persist($adherent5->followCommittee($object));

        $this->setReference('committee-v2-2', $object);

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            LoadAdherentData::class,
        ];
    }
}
