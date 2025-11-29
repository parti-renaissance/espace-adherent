<?php

declare(strict_types=1);

namespace App\DataFixtures\ORM;

use App\Entity\Pap\VotePlace;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Ramsey\Uuid\Uuid;

class LoadPapVotePlaceData extends Fixture implements DependentFixtureInterface
{
    private const UUID_1 = 'dcaec65c-0856-4c27-adf5-6d51593601e3';
    private const UUID_2 = '8788d1df-9807-45db-a79a-3e1c03df141b';
    private const UUID_3 = 'de7ed0bd-acec-4744-b94d-30b98d895adc';
    private const UUID_4 = '7157a379-e66d-4afd-b1a3-412fbf9ce0e5';
    private const UUID_5 = '1c74d299-0f95-4d14-8990-713b57713ebd';
    private const UUID_6 = '8daa4d93-4881-42b3-9e0b-5e6324828a62';
    private const UUID_7 = '33106ef9-ba14-4281-8032-e186735df717';
    private const UUID_8 = '9ece4e07-0c46-4e94-a0d0-087efbe30fff';
    private const UUID_9 = '3e254a91-9779-4ccd-96a5-bc19f8b9579d';
    private const UUID_10 = 'aac8bf0d-aa66-4860-a7ed-dbfe85ed544f';
    private const UUID_11 = '3db888e3-147f-4334-b2b3-16eff68a23c9';
    private const UUID_12 = 'e4eaed49-5cd1-4e0a-986a-d981433a50a4';
    private const UUID_13 = 'b467e84c-74dc-453f-8ee7-7adf338a481f';
    private const UUID_14 = '1cc8f1bf-533d-4c3a-a02b-00ba651e056a';

    public function load(ObjectManager $manager): void
    {
        $manager->persist($object = new VotePlace(
            48.86291,
            2.341939,
            '75108_0001',
            2,
            3,
            Uuid::fromString(self::UUID_1),
            LoadGeoZoneData::getZoneReference($manager, 'zone_district_75-1')
        ));
        $this->setReference('pap-vote-place--paris-8-a', $object);

        $manager->persist($object = new VotePlace(
            48.877132,
            2.341905,
            '75108_0002',
            2,
            4,
            Uuid::fromString(self::UUID_2),
            LoadGeoZoneData::getZoneReference($manager, 'zone_district_75-1')
        ));
        $this->setReference('pap-vote-place--paris-8-b', $object);

        $manager->persist($object = new VotePlace(
            48.882256,
            2.334756,
            '75108_0003',
            0,
            0,
            Uuid::fromString(self::UUID_3),
            LoadGeoZoneData::getZoneReference($manager, 'zone_district_75-1')
        ));
        $this->setReference('pap-vote-place--paris-8-c', $object);

        $manager->persist($object = new VotePlace(
            48.868813,
            2.338279,
            '75108_0004',
            0,
            0,
            Uuid::fromString(self::UUID_13),
            LoadGeoZoneData::getZoneReference($manager, 'zone_district_75-1')
        ));
        $this->setReference('pap-vote-place--paris-8-d', $object);

        $manager->persist($object = new VotePlace(
            48.879414,
            2.319874,
            '75108_0005',
            0,
            0,
            Uuid::fromString(self::UUID_14),
            LoadGeoZoneData::getZoneReference($manager, 'zone_district_75-1')
        ));
        $this->setReference('pap-vote-place--paris-8-e', $object);

        $manager->persist($object = new VotePlace(
            48.822865,
            2.362221,
            '75103_0001',
            1,
            1,
            Uuid::fromString(self::UUID_4),
            LoadGeoZoneData::getZoneReference($manager, 'zone_district_75-18')
        ));
        $this->setReference('pap-vote-place--paris-3-b', $object);

        $manager->persist($object = new VotePlace(
            48.75202,
            2.293244,
            '92002_0001',
            2,
            4,
            Uuid::fromString(self::UUID_5),
            LoadGeoZoneData::getZoneReference($manager, 'zone_district_92-2')
        ));
        $this->setReference('pap-vote-place--anthony-a', $object);

        $manager->persist($object = new VotePlace(
            48.760128,
            2.297235,
            '92002_0002',
            1,
            1,
            Uuid::fromString(self::UUID_6),
            LoadGeoZoneData::getZoneReference($manager, 'zone_district_92-2')
        ));
        $this->setReference('pap-vote-place--anthony-b', $object);

        $manager->persist($object = new VotePlace(
            48.75752,
            2.304083,
            '92002_0003',
            0,
            0,
            Uuid::fromString(self::UUID_7),
            LoadGeoZoneData::getZoneReference($manager, 'zone_district_92-2')
        ));
        $this->setReference('pap-vote-place--anthony-c', $object);

        $manager->persist($object = new VotePlace(
            48.94159,
            2.157997,
            '78586_0001',
            0,
            0,
            Uuid::fromString(self::UUID_8),
            LoadGeoZoneData::getZoneReference($manager, 'zone_district_78-6')
        ));
        $this->setReference('pap-vote-place--sartrouville-a', $object);

        $manager->persist($object = new VotePlace(
            48.93528,
            2.151656,
            '78586_0002',
            0,
            0,
            Uuid::fromString(self::UUID_9),
            LoadGeoZoneData::getZoneReference($manager, 'zone_district_78-6')
        ));
        $this->setReference('pap-vote-place--sartrouville-b', $object);

        $manager->persist($object = new VotePlace(
            48.934376,
            2.155423,
            '78586_0003',
            0,
            0,
            Uuid::fromString(self::UUID_10),
            LoadGeoZoneData::getZoneReference($manager, 'zone_district_78-6')
        ));
        $this->setReference('pap-vote-place--sartrouville-c', $object);

        $manager->persist($object = new VotePlace(
            48.348328,
            2.561779,
            '77001_0001',
            0,
            0,
            Uuid::fromString(self::UUID_11),
            LoadGeoZoneData::getZoneReference($manager, 'zone_district_77-1')
        ));
        $this->setReference('pap-vote-place--achere-la-forêt-a', $object);

        $manager->persist($object = new VotePlace(
            43.696266,
            7.241974,
            '06088_0001',
            1,
            0,
            Uuid::fromString(self::UUID_12),
            LoadGeoZoneData::getZoneReference($manager, 'zone_district_06-8')
        ));
        $this->setReference('pap-vote-place--nice-a', $object);

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            LoadGeoZoneData::class,
        ];
    }
}
