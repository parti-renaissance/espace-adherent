<?php

namespace App\DataFixtures\ORM;

use App\Address\PostAddressFactory;
use App\Entity\Geo\Zone;
use App\Entity\ProcurationV2\Request;
use App\Entity\ProcurationV2\Round;
use App\Utils\PhoneNumberUtils;
use App\ValueObject\Genders;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Faker\Generator;

class LoadProcurationV2RequestData extends Fixture implements DependentFixtureInterface
{
    private Generator $faker;

    public function __construct(
        private readonly PostAddressFactory $addressFactory
    ) {
        $this->faker = Factory::create('fr_FR');
    }

    public function load(ObjectManager $manager)
    {
        $manager->persist($this->createRequest(
            $rounds = [$this->getReference('procuration-v2-europeennes-2024-round-1')],
            'jack.doe@test.dev',
            Genders::MALE,
            'Jack, Didier',
            'Doe',
            '1990-05-15',
            '+33644332211',
            'FR',
            '06000',
            'Nice',
            '58 Boulevard de la Madeleine',
            false,
            LoadGeoZoneData::getZoneReference($manager, 'zone_city_06088'),
            $this->getReference('zone_vote_place_nice_1')
        ));

        $manager->persist($this->createRequest(
            $rounds,
            'pascal.dae@test.dev',
            Genders::MALE,
            'Pascal, Roger',
            'Dae',
            '1990-05-15',
            null,
            'CH',
            '8057',
            'Kilchberg',
            '13 Pilgerweg',
            false,
            LoadGeoZoneData::getZoneReference($manager, 'zone_country_CH'),
            null,
            'BDV CH 1',
            false
        ));

        $zone = LoadGeoZoneData::getZoneReference($manager, 'zone_city_92024');
        for ($i = 1; $i <= 10; ++$i) {
            $manager->persist(
                $request = $this->createRequest(
                    $rounds,
                    $this->faker->email(),
                    0 === $i % 2 ? Genders::MALE : Genders::FEMALE,
                    $this->faker->firstName(),
                    $this->faker->lastName(),
                    $this->faker->dateTimeBetween('-50 years', '-18 years')->format('Y-m-d'),
                    '+33644332211',
                    'FR',
                    '75008',
                    'Paris',
                    '68 rue du Rocher',
                    false,
                    $zone,
                )
            );
            $request->setCreatedAt($date = new \DateTime('-'.$i.' days'));
            $request->setUpdatedAt($date);
        }

        $manager->persist($this->createRequest(
            [$this->getReference('procuration-v2-legislatives-2024-round-1')],
            'jack.doe@test.dev',
            Genders::MALE,
            'Jack, Didier',
            'Doe',
            '1990-05-15',
            '+33644332211',
            'FR',
            '06000',
            'Nice',
            '58 Boulevard de la Madeleine',
            false,
            LoadGeoZoneData::getZoneReference($manager, 'zone_city_06088'),
            $this->getReference('zone_vote_place_nice_1')
        ));

        $manager->persist($this->createRequest(
            [$this->getReference('procuration-v2-legislatives-2024-round-2')],
            'pierre.doe@test.dev',
            Genders::MALE,
            'Pierre',
            'Doe',
            '1990-05-15',
            '+33644332211',
            'FR',
            '06000',
            'Nice',
            '58 Boulevard de la Madeleine',
            false,
            LoadGeoZoneData::getZoneReference($manager, 'zone_city_06088'),
            $this->getReference('zone_vote_place_nice_1')
        ));

        $manager->persist($this->createRequest(
            [
                $this->getReference('procuration-v2-legislatives-2024-round-1'),
                $this->getReference('procuration-v2-legislatives-2024-round-2')
            ],
            'chris.doe@test.dev',
            Genders::MALE,
            'Chris',
            'Doe',
            '1990-05-15',
            '+33644332211',
            'FR',
            '06000',
            'Nice',
            '58 Boulevard de la Madeleine',
            false,
            LoadGeoZoneData::getZoneReference($manager, 'zone_city_06088'),
            $this->getReference('zone_vote_place_nice_1')
        ));

        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            LoadGeoZoneData::class,
            LoadProcurationV2ElectionData::class,
        ];
    }

    private function createRequest(
        array $rounds,
        string $email,
        string $gender,
        string $firstNames,
        string $lastName,
        string $birthdate,
        ?string $phone,
        string $country,
        string $postalCode,
        string $city,
        string $address,
        bool $distantVotePlace,
        Zone $voteZone,
        ?Zone $votePlace = null,
        ?string $customVotePlace = null,
        bool $fromFrance = true,
        bool $joinNewsletter = false
    ): Request {
        return new Request(
            $rounds,
            $email,
            $gender,
            $firstNames,
            $lastName,
            new \DateTimeImmutable($birthdate),
            $phone ? PhoneNumberUtils::create($phone) : null,
            $this->addressFactory->createFlexible($country, $postalCode, $city, $address),
            $distantVotePlace,
            $voteZone,
            $votePlace,
            $customVotePlace,
            $fromFrance,
            null,
            $joinNewsletter
        );
    }
}
