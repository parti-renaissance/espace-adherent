<?php

namespace App\DataFixtures\ORM;

use App\Address\AddressInterface;
use App\Entity\Election\VotePlace;
use App\Entity\NullablePostAddress;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Ramsey\Uuid\Uuid;

class LoadElectionVotePlaceData extends Fixture implements DependentFixtureInterface
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

    private function createVotePlace(
        ObjectManager $manager,
        ?string $uuid,
        string $code,
        string $name,
        string $zoneId,
        float $latitude = null,
        float $longitude = null,
        int $nbAddresses = null,
        int $nbVoters = null
    ): VotePlace {
        $object = new VotePlace($uuid ? Uuid::fromString($uuid) : Uuid::uuid4(), $code, $name);
        $object->latitude = $latitude;
        $object->longitude = $longitude;
        $object->nbAddresses = (int) $nbAddresses;
        $object->nbVoters = (int) $nbVoters;
        $object->zone = LoadGeoZoneData::getZoneReference($manager, $zoneId);

        return $object;
    }

    public function load(ObjectManager $manager)
    {
        $manager->persist($object = $this->createVotePlace(
            $manager,
            self::UUID_1,
            '75108_0001',
            'Bureau de vote 1',
            'zone_district_75-1',
            48.86291,
            2.341939,
            2,
            3
        ));

        $this->setReference('vote-place--paris-8-a', $object);

        $manager->persist($object = $this->createVotePlace(
            $manager,
            self::UUID_2,
            '75108_0002',
            'Bureau de vote 2',
            'zone_district_75-1',
            48.877132,
            2.341905,
            2,
            4
        ));

        $this->setReference('vote-place--paris-8-b', $object);

        $manager->persist($object = $this->createVotePlace(
            $manager,
            self::UUID_3,
            '75108_0003',
            'Bureau de vote 3',
            'zone_district_75-1',
            48.882256,
            2.334756,
            0,
            0
        ));

        $this->setReference('vote-place--paris-8-c', $object);

        $manager->persist($object = $this->createVotePlace(
            $manager,
            self::UUID_4,
            '75103_0001',
            'Bureau de vote 4',
            'zone_district_75-18',
            48.822865,
            2.362221,
            1,
            1
        ));

        $this->setReference('vote-place--paris-3-b', $object);

        $manager->persist($object = $this->createVotePlace(
            $manager,
            self::UUID_5,
            '92002_0001',
            'Bureau de vote 5',
            'zone_district_92-2',
            48.75202,
            2.293244,
            2,
            4
        ));

        $this->setReference('vote-place--anthony-a', $object);

        $manager->persist($object = $this->createVotePlace(
            $manager,
            self::UUID_6,
            '92002_0002',
            'Bureau de vote 6',
            'zone_district_92-2',
            48.760128,
            2.297235,
            1,
            1
        ));

        $this->setReference('vote-place--anthony-b', $object);

        $manager->persist($object = $this->createVotePlace(
            $manager,
            self::UUID_7,
            '92002_0003',
            'Bureau de vote 7',
            'zone_district_92-2',
            48.75752,
            2.304083,
            0,
            0
        ));

        $this->setReference('vote-place--anthony-c', $object);

        $manager->persist($object = $this->createVotePlace(
            $manager,
            self::UUID_8,
            '78586_0001',
            'Bureau de vote 8',
            'zone_district_78-6',
            48.94159,
            2.157997,
            0,
            0
        ));

        $this->setReference('vote-place--sartrouville-a', $object);

        $manager->persist($object = $this->createVotePlace(
            $manager,
            self::UUID_9,
            '78586_0002',
            'Bureau de vote 9',
            'zone_district_78-6',
            48.93528,
            2.151656,
            0,
            0
        ));

        $this->setReference('vote-place--sartrouville-b', $object);

        $manager->persist($object = $this->createVotePlace(
            $manager,
            self::UUID_10,
            '78586_0003',
            'Bureau de vote 10',
            'zone_district_78-6',
            48.934376,
            2.155423,
            0,
            0
        ));

        $this->setReference('vote-place--sartrouville-c', $object);

        $manager->persist($object = $this->createVotePlace(
            $manager,
            self::UUID_11,
            '77001_0001',
            'Bureau de vote 11',
            'zone_district_77-1',
            48.348328,
            2.561779,
            0,
            0
        ));

        $this->setReference('vote-place--achere-la-forêt-a', $object);

        $manager->persist($object = $this->createVotePlace(
            $manager,
            self::UUID_12,
            '06088_0001',
            'Bureau de vote 12',
            'zone_district_06-8',
            43.696266,
            7.241974,
            1,
            0
        ));

        $this->setReference('vote-place--nice-a', $object);

        $manager->persist($object = $this->createVotePlace(
            $manager,
            self::UUID_13,
            '75108_0004',
            'Bureau de vote 13',
            'zone_district_75-1',
            48.868813,
            2.338279,
            0,
            0
        ));

        $this->setReference('vote-place--paris-8-d', $object);

        $manager->persist($object = $this->createVotePlace(
            $manager,
            self::UUID_14,
            '75108_0005',
            'Bureau de vote 14',
            'zone_district_75-1',
            48.879414,
            2.319874,
            0,
            0
        ));

        $this->setReference('vote-place--paris-8-e', $object);

        $manager->persist($object = $this->createVotePlace(
            $manager,
            null,
            '59350_0113',
            'Salle Polyvalente De Wazemmes',
            'zone_district_59-1'
        ));
        $object->setPostAddress(NullablePostAddress::createAddress(AddressInterface::FRANCE, '59000,59100', 'Lille', "Rue De L'Abbé Aerts", null, null));

        $this->addReference('vote-place-lille-wazemmes', $object);

        $manager->persist($object = $this->createVotePlace(
            $manager,
            null,
            '59350_0407',
            'Restaurant Scolaire - Rue H. Lefebvre',
            'zone_district_59-4'
        ));
        $object->setPostAddress(NullablePostAddress::createAddress(AddressInterface::FRANCE, '59350', 'Lille', 'Groupe Scolaire Jean Zay', null, null));

        $this->addReference('vote-place-lille-jean-zay', $object);

        $manager->persist($object = $this->createVotePlace(
            $manager,
            null,
            '93066_0004',
            'Ecole Maternelle La Source',
            'zone_district_93-1'
        ));

        $object->setPostAddress(NullablePostAddress::createAddress(AddressInterface::FRANCE, '93200,93066', 'Saint-Denis', '15, Rue Auguste Blanqui', null, null));

        $this->addReference('vote-place-bobigny-blanqui', $object);

        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            LoadGeoZoneData::class,
        ];
    }
}
