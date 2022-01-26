<?php

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
    private const ADDRESS_10_UUID = 'fc39cb41-38fc-446e-a8aa-6c6f0f62d0fd';
    private const ADDRESS_11_UUID = '69bf1775-0f00-45c8-9e10-28fd32ba8d93';
    private const ADDRESS_12_UUID = 'e864d759-5a77-4676-86aa-561428b25377';
    private const ADDRESS_13_UUID = '49dfb050-2f34-4cdd-b0fc-ce4d1f18b49a';
    private const ADDRESS_14_UUID = '86158b8a-82bd-4773-953f-d6933287dd0e';
    private const ADDRESS_15_UUID = '7d952167-b27e-4b05-9440-73bf289c776f';
    private const ADDRESS_16_UUID = 'a402624a-1c30-4df9-a451-1e57b2ef5f89';
    private const ADDRESS_17_UUID = '37e79eae-033f-4bc6-9551-f1f1ac18faf1';
    private const ADDRESS_18_UUID = '1d4bc6a0-7407-48c9-a05f-3ab7c38efbae';
    private const ADDRESS_19_UUID = '83398a6e-aad8-4c05-93d0-e03a8f0d19da';
    private const ADDRESS_20_UUID = 'f190c07b-f686-4bf1-a8f4-17a039087994';
    private const ADDRESS_21_UUID = 'd5a0a275-66b9-4097-b037-9e89e90a40ef';

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
    private const VOTER_14_UUID = 'd3c4c03e-c0ae-4662-9017-eaf7dcc31c61';
    private const VOTER_15_UUID = '6c5eb999-41b7-4cd3-b308-3769335d8349';
    private const VOTER_16_UUID = 'e176d6ef-95cb-4532-8428-1d37bed79f2b';
    private const VOTER_17_UUID = 'f3d19140-3490-44bc-b4c6-f5a11a4df728';
    private const VOTER_18_UUID = '52ef90f9-2ca6-4663-9de9-ecea75292b73';
    private const VOTER_19_UUID = 'b2891aba-071f-4504-8e31-797bf16beda7';
    private const VOTER_20_UUID = '0988c9ab-cf25-4ad0-ac37-b46b1c1b0988';
    private const VOTER_21_UUID = '46a8f4e7-fefb-4dfc-8f9b-8fbeaca37e77';
    private const VOTER_22_UUID = '1c9594b4-23e8-4dbc-b812-5325c701bcc8';
    private const VOTER_23_UUID = 'eb700d9b-892b-4796-aa39-90e607da39cf';
    private const VOTER_24_UUID = 'b3d74035-5726-43b0-9f79-1dfbf7647501';
    private const VOTER_25_UUID = 'a1868ad7-dbff-45e2-a8cc-a3fd3f3ffc65';
    private const VOTER_26_UUID = 'b056bfa2-34c7-4330-8c83-c4fcc30edcdc';
    private const VOTER_27_UUID = 'ea6441a5-62b3-4a2f-8ac1-4623956c77b7';
    private const VOTER_28_UUID = 'e68def4a-0355-4f63-bf50-a77a18a119ca';
    private const VOTER_29_UUID = '42cbe193-21e8-4285-ba45-fb98c4f52509';
    private const VOTER_30_UUID = '9c33e65e-e041-4caa-9355-e122e9fa0eb4';

    public function load(ObjectManager $manager)
    {
        /** @var VotePlace $vpParis8a */
        $vpParis8a = $this->getReference('pap-vote-place--paris-8-a');
        /** @var VotePlace $vpParis8b */
        $vpParis8b = $this->getReference('pap-vote-place--paris-8-b');
        /** @var VotePlace $vpParis3b */
        $vpParis3b = $this->getReference('pap-vote-place--paris-3-b');
        /** @var VotePlace $vpAnthonya */
        $vpAnthonya = $this->getReference('pap-vote-place--anthony-a');
        /** @var VotePlace $vpAnthonyb */
        $vpAnthonyb = $this->getReference('pap-vote-place--anthony-b');
        /** @var VotePlace $vpAnthonyc */
        $vpAnthonyc = $this->getReference('pap-vote-place--anthony-c');
        /** @var VotePlace $vpSartrouvillea */
        $vpSartrouvillea = $this->getReference('pap-vote-place--sartrouville-a');
        /** @var VotePlace $vSartrouvilleb */
        $vpSartrouvilleb = $this->getReference('pap-vote-place--sartrouville-b');
        /** @var VotePlace $vpSartrouvilec */
        $vpSartrouvilec = $this->getReference('pap-vote-place--sartrouville-c');
        /** @var VotePlace $vpAchereLAForeta */
        $vpAchereLAForeta = $this->getReference('pap-vote-place--achere-la-forêt-a');
        /** @var VotePlace $vpParis3b */
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

//        $address = $this->createAddress(
//            self::ADDRESS_10_UUID,
//            '32',
//            'Rue Constituante',
//            '78586',
//            ['78500'],
//            'Sartrouville',
//            66321,
//            45045,
//            48.941498,
//            2.158159
//        );
//        $address->addZone(LoadGeoZoneData::getZoneReference($manager, 'zone_city_78586'));
//        $address->addVoter($this->createVoter(self::VOTER_14_UUID, 'Jean-Paul', 'AZOULAY', Genders::MALE, '-35 years', '92002_0002'));
//        $address->addVoter($this->createVoter(self::VOTER_15_UUID, 'Océane', 'BLEU', Genders::FEMALE, '-42 years', '92002_0002'));
//        $address->addVoter($this->createVoter(self::VOTER_16_UUID, 'Michel', 'LACOMBE', Genders::FEMALE, '-38 years', '92002_0002'));
//        $address->votePlace = $vpSartrouvillea;
//        $this->addReference('address-78-1', $address);
//        $manager->persist($address);
//
//        $address = $this->createAddress(
//            self::ADDRESS_11_UUID,
//            '35',
//            'Avenue Jules Ferry',
//            '78586',
//            ['78500'],
//            'Sartrouville',
//            66320,
//            45049,
//            48.934387,
//            2.155293
//        );
//        $address->addZone(LoadGeoZoneData::getZoneReference($manager, 'zone_city_78586'));
//        $address->addVoter($this->createVoter(self::VOTER_17_UUID, 'Jean-Pierre', 'AZOULAY', Genders::MALE, '-35 years', '92002_0002'));
//        $address->addVoter($this->createVoter(self::VOTER_18_UUID, 'Mike', 'MATHIAS', Genders::MAle, '-42 years', '92002_0002'));
//        $address->addVoter($this->createVoter(self::VOTER_19_UUID, 'Laila', 'LACOMBE', Genders::FEMALE, '-38 years', '92002_0002'));
//        $address->addVoter($this->createVoter(self::VOTER_20_UUID, 'Victor', 'COHEN', Genders::MALE, '-38 years', '92002_0002'));
//        $address->addVoter($this->createVoter(self::VOTER_21_UUID, 'LUCIE', 'VALLET', Genders::FEMALE, '-38 years', '92002_0002'));
//        $address->votePlace = $vpSartrouvillea;
//        $this->addReference('address-78-2', $address);
//        $manager->persist($address);

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
        float $longitude
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
        string $votePlace = null
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

    public function getDependencies()
    {
        return [
            LoadPapVotePlaceData::class,
        ];
    }
}
