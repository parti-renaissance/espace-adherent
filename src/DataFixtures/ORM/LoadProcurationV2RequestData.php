<?php

declare(strict_types=1);

namespace App\DataFixtures\ORM;

use App\Address\PostAddressFactory;
use App\Entity\Geo\Zone;
use App\Entity\ProcurationV2\Request;
use App\Entity\ProcurationV2\RequestSlot;
use App\Entity\ProcurationV2\Round;
use App\Utils\PhoneNumberUtils;
use App\ValueObject\Genders;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Faker\Generator;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

class LoadProcurationV2RequestData extends Fixture implements DependentFixtureInterface
{
    public const UUID_REQUEST_1 = '40f856da-8f9d-4133-b74b-d090063605c7';
    public const UUID_REQUEST_2 = '5bc0b6e2-7073-4572-8d98-f5b64d591ca7';
    public const UUID_REQUEST_SLOT_1 = 'f406fc52-248b-4e30-bcb6-355516a45ad9';

    private Generator $faker;

    public function __construct(
        private readonly PostAddressFactory $addressFactory,
    ) {
        $this->faker = Factory::create('fr_FR');
    }

    public function load(ObjectManager $manager): void
    {
        $manager->persist($this->createRequest(
            null,
            $rounds = [$this->getReference('procuration-v2-europeennes-2024-round-1', Round::class)],
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
            LoadGeoZoneData::getZone($manager, 'zone_city_06088'),
            $this->getReference('zone_vote_place_nice_1', Zone::class)
        ));

        $manager->persist($this->createRequest(
            null,
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
            LoadGeoZoneData::getZone($manager, 'zone_country_CH'),
            null,
            'BDV CH 1',
            false
        ));

        $zone = LoadGeoZoneData::getZone($manager, 'zone_city_92024');
        for ($i = 1; $i <= 10; ++$i) {
            $manager->persist(
                $request = $this->createRequest(
                    null,
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

        $manager->persist($request = $this->createRequest(
            Uuid::fromString(self::UUID_REQUEST_1),
            [$this->getReference('procuration-v2-legislatives-2024-round-1', Round::class)],
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
            LoadGeoZoneData::getZone($manager, 'zone_city_92024'),
            $this->getReference('zone_vote_place_clichy_1', Zone::class)
        ));
        $this->setReference('request_slot_1', $request->requestSlots->first());

        $manager->persist($this->createRequest(
            Uuid::fromString(self::UUID_REQUEST_2),
            [$this->getReference('procuration-v2-legislatives-2024-round-2', Round::class)],
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
            LoadGeoZoneData::getZone($manager, 'zone_city_92024'),
            $this->getReference('zone_vote_place_clichy_1', Zone::class)
        ));

        $manager->persist($this->createRequest(
            null,
            [
                $this->getReference('procuration-v2-legislatives-2024-round-2', Round::class),
                $round1 = $this->getReference('procuration-v2-legislatives-2024-round-1', Round::class),
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
            LoadGeoZoneData::getZone($manager, 'zone_city_92024'),
            $this->getReference('zone_vote_place_clichy_1', Zone::class),
            null,
            true,
            false,
            [
                $round1->name => self::UUID_REQUEST_SLOT_1,
            ]
        ));

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            LoadGeoZoneData::class,
            LoadAdherentData::class,
            LoadProcurationV2ElectionData::class,
            LoadProcurationV2ProxyData::class,
        ];
    }

    private function createRequest(
        ?UuidInterface $uuid,
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
        bool $joinNewsletter = false,
        array $slotsUuidMapping = [],
    ): Request {
        $request = new Request(
            $uuid ?? Uuid::uuid4(),
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

        $request->refreshZoneIds();

        foreach ($rounds as $round) {
            $uuid = \array_key_exists($round->name, $slotsUuidMapping)
                ? Uuid::fromString($slotsUuidMapping[$round->name])
                : null;

            $request->requestSlots->add(new RequestSlot($round, $request, $uuid));
        }

        return $request;
    }
}
