<?php

declare(strict_types=1);

namespace App\DataFixtures\ORM;

use App\Committee\CommitteeMembershipTriggerEnum;
use App\Entity\Adherent;
use App\Entity\Committee;
use App\Entity\CommitteeElection;
use App\Entity\VotingPlatform\Designation\Designation;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Ramsey\Uuid\Uuid;

class LoadCommitteeData extends AbstractLoadPostAddressData implements DependentFixtureInterface
{
    public const COMMITTEE_1_UUID = '5e00c264-1d4b-43b8-862e-29edc38389b3';
    public const COMMITTEE_2_UUID = '8c4b48ec-9290-47ae-a5db-d1cf2723e8b3';
    public const COMMITTEE_3_UUID = '3593a8b3-e7cd-4d84-a1dd-71d3b8fc2070';

    public const COMMITTEE_ELECTION_1_UUID = '278fcb58-53b4-4798-a3be-e5bb92f7f0f2';
    public const COMMITTEE_ELECTION_2_UUID = 'f86ee969-5eca-4666-bcd4-7f7388372e0b';
    public const COMMITTEE_ELECTION_3_UUID = '9d31ac39-f9ac-4b3b-b1cc-351bc30704b6';

    public function load(ObjectManager $manager): void
    {
        $manager->persist($object = Committee::createSimple(
            Uuid::fromString(self::COMMITTEE_1_UUID),
            LoadAdherentData::ADHERENT_20_UUID,
            'Comité des 3 communes',
            'Un petit comité avec seulement 3 communes',
        ));
        $object->approved();
        $object->addZone(LoadGeoZoneData::getZoneReference($manager, 'zone_city_92002'));
        $object->addZone(LoadGeoZoneData::getZoneReference($manager, 'zone_city_92004'));
        $object->addZone(LoadGeoZoneData::getZoneReference($manager, 'zone_city_92007'));
        $object->setCurrentElection(new CommitteeElection($this->getReference('designation-committee-01', Designation::class), Uuid::fromString(self::COMMITTEE_ELECTION_3_UUID)));
        $object->animator = $this->getReference('adherent-55', Adherent::class);

        foreach (range(51, 60) as $index) {
            $manager->persist($this->getReference('adherent-'.$index, Adherent::class)->followCommittee($object, new \DateTime('-2 months'), CommitteeMembershipTriggerEnum::COMMITTEE_EDITION));
        }

        $this->setReference('committee-v2-1', $object);

        $manager->persist($object = Committee::createSimple(
            Uuid::fromString(self::COMMITTEE_2_UUID),
            LoadAdherentData::ADHERENT_5_UUID,
            'Second Comité des 3 communes',
            'Un petit comité avec seulement 3 communes',
        ));

        $object->approved('2017-01-27 09:18:33');
        $object->addZone(LoadGeoZoneData::getZoneReference($manager, 'zone_city_92012'));
        $object->addZone(LoadGeoZoneData::getZoneReference($manager, 'zone_city_92014'));
        $object->addZone(LoadGeoZoneData::getZoneReference($manager, 'zone_city_92024'));

        $object->animator = $this->getReference('adherent-56', Adherent::class);
        $object->setCurrentElection(new CommitteeElection($this->getReference('designation-committee-02', Designation::class), Uuid::fromString(self::COMMITTEE_ELECTION_1_UUID)));
        $object->addElection($election = new CommitteeElection($this->getReference('designation-committee-03', Designation::class), Uuid::fromString(self::COMMITTEE_ELECTION_2_UUID)));

        $this->setReference('committee-election-2', $election);

        $adherentRe4 = $this->getReference('renaissance-user-4', Adherent::class);
        $manager->persist($adherentRe4->followCommittee($object, trigger: CommitteeMembershipTriggerEnum::COMMITTEE_EDITION));

        $adherent5 = $this->getReference('adherent-5', Adherent::class);
        $manager->persist($adherent5->followCommittee($object, trigger: CommitteeMembershipTriggerEnum::COMMITTEE_EDITION));

        $adherent16 = $this->getReference('adherent-16', Adherent::class);
        $manager->persist($adherent16->followCommittee($object, trigger: CommitteeMembershipTriggerEnum::COMMITTEE_EDITION));

        $this->setReference('committee-v2-2', $object);

        $manager->persist($object = Committee::createSimple(
            Uuid::fromString(self::COMMITTEE_3_UUID),
            LoadAdherentData::ADHERENT_20_UUID,
            'Comité du QG',
            'Un comité du QG',
        ));
        $object->approved();
        $object->addZone(LoadGeoZoneData::getZoneReference($manager, 'zone_borough_75108'));

        $manager->flush();

        $manager->getRepository(Committee::class)->updateMembershipsCounters();
    }

    public function getDependencies(): array
    {
        return [
            LoadAdherentData::class,
            LoadDesignationData::class,
            LoadGeoZoneData::class,
        ];
    }
}
