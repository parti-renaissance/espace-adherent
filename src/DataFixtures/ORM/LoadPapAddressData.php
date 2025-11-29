<?php

declare(strict_types=1);

namespace App\DataFixtures\ORM;

use App\Entity\Pap\Address;
use App\Entity\Pap\VotePlace;
use App\Entity\Pap\Voter;
use App\ValueObject\Genders;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Ramsey\Uuid\Uuid;

class LoadPapAddressData extends Fixture implements DependentFixtureInterface
{
    private const ADDRESS_01_UUID = 'a0b9231b-9ff5-49b9-aa7a-1d28abbba32f';
    private const ADDRESS_02_UUID = 'ccfd846a-5439-42ad-85ce-286baf4e7269';
    private const ADDRESS_03_UUID = '702eda29-39c6-4b3d-b28f-3fd3806747b2';
    private const ADDRESS_04_UUID = '04e1d76f-c727-4612-afab-2dec2d71a480';
    private const ADDRESS_05_UUID = 'd2c0d38c-2224-41c2-acb5-78b5dad06819';
    private const ADDRESS_06_UUID = '0590d744-61cf-4b56-8c63-d82c1886c2f3';
    private const ADDRESS_07_UUID = '6bb9c99f-48cb-44a8-9a94-10258020ebc6';
    private const ADDRESS_08_UUID = 'a10477b9-4a17-4ace-b0c8-22fbdccf154d';
    private const ADDRESS_09_UUID = '967614e4-0783-47d6-96f7-edec7ec390d1';
    private const ADDRESS_10_UUID = '0bd60032-2242-4eb2-bb43-be7efaf8833f';
    private const ADDRESS_11_UUID = 'f93d880e-5d8c-4e6f-bfc8-3b93d8131437';
    private const ADDRESS_12_UUID = '5de25515-c28e-4cda-a4b6-6847c04d35eb';

    private const VOTER_01_UUID = 'bdb9d49c-20f5-44c0-bc4a-d8b75f85ee95';
    private const VOTER_02_UUID = '0cf560f0-c5ec-43ef-9ea1-b6fd2a2dc339';
    private const VOTER_03_UUID = '75c6bacb-f278-4194-b1df-014de729aead';
    private const VOTER_04_UUID = '7d3c0207-f3aa-4804-b713-f01ab29052e6';
    private const VOTER_05_UUID = '348fd353-9819-4dfc-848d-211672ebb6b9';
    private const VOTER_06_UUID = '881cd07e-efce-4fda-890b-70ad277c8c32';
    private const VOTER_07_UUID = '536f8caa-a644-449e-8bdf-aca3475d9276';
    private const VOTER_08_UUID = '1e0a100f-aad5-44a8-93d4-e746199e409b';
    private const VOTER_09_UUID = 'ed0a6ef3-a046-4926-a1d9-078d6e6b3315';
    private const VOTER_10_UUID = '12c73366-8120-4fad-9ec0-b51a982964e7';
    private const VOTER_11_UUID = 'c35e572a-c362-414d-aee5-8fe514f2bf6b';
    private const VOTER_12_UUID = '416b6055-f339-48b7-910a-482cad149cec';
    private const VOTER_13_UUID = '4cac97a2-b501-49d6-908a-26d5d314bfc4';
    private const VOTER_14_UUID = 'd3df07e7-71e2-47f8-9e53-d962c35e655e';

    public function load(ObjectManager $manager): void
    {
        /** @var VotePlace $vpParis8a */
        $vpParis8a = $this->getReference('pap-vote-place--paris-8-a', VotePlace::class);
        /** @var VotePlace $vpParis8b */
        $vpParis8b = $this->getReference('pap-vote-place--paris-8-b', VotePlace::class);
        /** @var VotePlace $vpParis8e */
        $vpParis8e = $this->getReference('pap-vote-place--paris-8-e', VotePlace::class);
        /** @var VotePlace $vpParis3b */
        $vpParis3b = $this->getReference('pap-vote-place--paris-3-b', VotePlace::class);
        /** @var VotePlace $vpAnthonya */
        $vpAnthonya = $this->getReference('pap-vote-place--anthony-a', VotePlace::class);
        /** @var VotePlace $vpAnthonyb */
        $vpAnthonyb = $this->getReference('pap-vote-place--anthony-b', VotePlace::class);
        /** @var VotePlace $vpNicea */
        $vpNicea = $this->getReference('pap-vote-place--nice-a', VotePlace::class);

        $address = $this->createAddress(
            self::ADDRESS_01_UUID,
            '55',
            'Rue du Rocher',
            '75108',
            ['75008'],
            'Paris 8ème',
            66380,
            45080,
            48.878708,
            2.319111
        );
        $address->addZone(LoadGeoZoneData::getZoneReference($manager, 'zone_borough_75108'));
        $address->addVoter($this->createVoter(self::VOTER_01_UUID, 'John', 'Doe', Genders::MALE, '-30 years', '75108_0001'));
        $address->addVoter($this->createVoter(self::VOTER_02_UUID, 'Jane', 'Doe', Genders::FEMALE, '-29 years', '75108_0001'));
        $address->votePlace = $vpParis8a;
        $this->addReference('address-1', $address);
        $manager->persist($address);

        $address = $this->createAddress(
            self::ADDRESS_02_UUID,
            '65',
            'Rue du Rocher',
            '75108',
            ['75008'],
            'Paris 8ème',
            66380,
            45080,
            48.879078,
            2.318631
        );
        $address->addZone(LoadGeoZoneData::getZoneReference($manager, 'zone_borough_75108'));
        $address->addVoter($this->createVoter(self::VOTER_03_UUID, 'Jack', 'Doe', Genders::MALE, '-55 years', '75108_0001'));
        $address->votePlace = $vpParis8a;
        $this->addReference('address-2', $address);
        $manager->persist($address);

        $address = $this->createAddress(
            self::ADDRESS_03_UUID,
            '67',
            'Rue du Rocher',
            '75108',
            ['75008'],
            'Paris 8ème',
            66380,
            45079,
            48.879246,
            2.318427
        );
        $address->addZone(LoadGeoZoneData::getZoneReference($manager, 'zone_borough_75108'));
        $address->addVoter($this->createVoter(self::VOTER_04_UUID, 'Mickaël', 'Doe', Genders::MALE, '-44 years', '75108_0001'));
        $address->addVoter($this->createVoter(self::VOTER_05_UUID, 'Mickaëla', 'Doe', Genders::FEMALE, '-45 years', '75108_0001'));
        $address->addVoter($this->createVoter(self::VOTER_06_UUID, 'Mickaël Jr', 'Doe', Genders::MALE, '-22 years', '75108_0001'));
        $address->votePlace = $vpParis8b;
        $this->addReference('address-3', $address);
        $manager->persist($address);

        $address = $this->createAddress(
            self::ADDRESS_04_UUID,
            '70',
            'Rue du Rocher',
            '75108',
            ['75008'],
            'Paris 8ème',
            66380,
            45080,
            48.879166,
            2.318761
        );
        $address->addZone(LoadGeoZoneData::getZoneReference($manager, 'zone_borough_75108'));
        $address->addVoter($this->createVoter(self::VOTER_07_UUID, 'Patrick', 'Simpson Jones', Genders::MALE, '-70 years', '75108_0001'));
        $this->addReference('address-4', $address);
        $address->votePlace = $vpParis8b;
        $manager->persist($address);

        $address = $this->createAddress(
            self::ADDRESS_11_UUID,
            '62',
            'Rue de Rome',
            '75108',
            ['75008'],
            'Paris 8ème',
            66381,
            45079,
            48.880085,
            2.321696
        );
        $address->addZone(LoadGeoZoneData::getZoneReference($manager, 'zone_borough_75108'));
        $address->addVoter($this->createVoter(self::VOTER_14_UUID, 'Jack', 'Dawson', Genders::MALE, '-21 years', '75108_0001'));
        $address->votePlace = $vpParis8e;
        $this->addReference('address-paris-5', $address);
        $manager->persist($address);

        $address = $this->createAddress(
            self::ADDRESS_12_UUID,
            '64',
            'Rue de Rome',
            '75108',
            ['75008'],
            'Paris 8ème',
            66381,
            45079,
            48.88023,
            2.321604
        );
        $address->addZone(LoadGeoZoneData::getZoneReference($manager, 'zone_borough_75108'));
        $address->votePlace = $vpParis8e;
        $this->addReference('address-paris-6', $address);
        $manager->persist($address);

        $address = $this->createAddress(
            self::ADDRESS_05_UUID,
            '92',
            'Boulevard Victor Hugo',
            '92024',
            ['92024'],
            'Clichy',
            66379,
            45067,
            48.90117,
            2.316956
        );
        $address->addZone(LoadGeoZoneData::getZoneReference($manager, 'zone_city_92024'));
        $address->addZone(LoadGeoZoneData::getZoneReference($manager, 'zone_department_92'));
        $this->addReference('address-92-1', $address);
        $manager->persist($address);

        $address = $this->createAddress(
            self::ADDRESS_06_UUID,
            '25',
            'Rue Béranger',
            '75103',
            ['75003'],
            'Paris',
            66396,
            45086,
            48.866875,
            2.363124
        );
        $address->addZone(LoadGeoZoneData::getZoneReference($manager, 'zone_borough_75103'));
        $address->addVoter($this->createVoter(self::VOTER_08_UUID, 'Ludo', 'Forest', Genders::MALE, '-55 years', '75103_0002'));
        $address->votePlace = $vpParis3b;
        $this->addReference('address-75-3', $address);
        $manager->persist($address);

        $address = $this->createAddress(
            self::ADDRESS_07_UUID,
            '18',
            'Rue des Augustins',
            '92002',
            ['92160'],
            'Anthony',
            66370,
            45150,
            48.751938,
            2.293015
        );
        $address->addZone(LoadGeoZoneData::getZoneReference($manager, 'zone_city_92002'));
        $address->addVoter($this->createVoter(self::VOTER_09_UUID, 'Lucas', 'Dubois', Genders::MALE, '-30 years', '92002_0001'));
        $address->addVoter($this->createVoter(self::VOTER_10_UUID, 'Vincent', 'PEYROUSSE', Genders::FEMALE, '-33 years', '92002_0001'));
        $address->votePlace = $vpAnthonya;
        $this->addReference('address-92-2', $address);
        $manager->persist($address);

        $address = $this->createAddress(
            self::ADDRESS_08_UUID,
            '3',
            'Rue Angélique',
            '92002',
            ['92160'],
            'Anthony',
            66371,
            45150,
            48.752205,
            2.293538
        );
        $address->addZone(LoadGeoZoneData::getZoneReference($manager, 'zone_city_92002'));
        $address->addVoter($this->createVoter(self::VOTER_11_UUID, 'Roger', 'VASSEUX', Genders::MALE, '-26 years', '92002_0001'));
        $address->addVoter($this->createVoter(self::VOTER_12_UUID, 'Harold', 'BEAUVOIS', Genders::MALE, '-33 years', '92002_0001'));
        $address->votePlace = $vpAnthonya;
        $this->addReference('address-92-3', $address);
        $manager->persist($address);

        $address = $this->createAddress(
            self::ADDRESS_09_UUID,
            '31',
            'Avenue Galliéni',
            '92002',
            ['92160'],
            'Anthony',
            66372,
            45145,
            48.760296,
            2.29762
        );
        $address->addZone(LoadGeoZoneData::getZoneReference($manager, 'zone_city_92002'));
        $address->addVoter($this->createVoter(self::VOTER_13_UUID, 'Kevin', 'HEDIEUX', Genders::MALE, '-60 years', '92002_0002'));
        $address->votePlace = $vpAnthonyb;
        $this->addReference('address-92-4', $address);
        $manager->persist($address);

        $address = $this->createAddress(
            self::ADDRESS_10_UUID,
            '57',
            'Boulevard de la Madeleine',
            '06088',
            ['06000'],
            'Nice',
            68172,
            47813,
            43.69751,
            7.240651
        );
        $address->addZone(LoadGeoZoneData::getZoneReference($manager, 'zone_city_06088'));
        $address->votePlace = $vpNicea;
        $this->addReference('address-06-1', $address);
        $manager->persist($address);

        $manager->flush();
    }

    private function createAddress(
        string $uuid,
        string $number,
        string $street,
        string $inseeCode,
        ?array $postalCodes,
        string $cityName,
        int $offsetX,
        int $offsetY,
        float $latitude,
        float $longitude,
    ): Address {
        return new Address(
            Uuid::fromString($uuid),
            $number,
            $street,
            $inseeCode,
            $postalCodes,
            $cityName,
            $offsetX,
            $offsetY,
            $latitude,
            $longitude
        );
    }

    private function createVoter(
        string $uuid,
        string $firstName,
        string $lastName,
        string $gender,
        string $birthdate,
        ?string $votePlace = null,
    ): Voter {
        return new Voter(
            Uuid::fromString($uuid),
            $firstName,
            $lastName,
            $gender,
            new \DateTime($birthdate),
            $votePlace
        );
    }

    public function getDependencies(): array
    {
        return [
            LoadPapVotePlaceData::class,
        ];
    }
}
