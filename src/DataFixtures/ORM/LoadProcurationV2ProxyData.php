<?php

declare(strict_types=1);

namespace App\DataFixtures\ORM;

use App\Address\PostAddressFactory;
use App\Entity\Adherent;
use App\Entity\Geo\Zone;
use App\Entity\ProcurationV2\Proxy;
use App\Entity\ProcurationV2\ProxySlot;
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

class LoadProcurationV2ProxyData extends Fixture implements DependentFixtureInterface
{
    public const UUID_PROXY_1 = '21b2fb24-cf5e-4fbb-af36-efa74605dc99';
    public const UUID_PROXY_2 = 'c1ddce73-84dd-45a0-8eed-2078a3de8625';
    public const UUID_PROXY_SLOT_1 = 'b024ff2a-c74b-442c-8339-7df9d0c104b6';

    private Generator $faker;

    public function __construct(
        private readonly PostAddressFactory $addressFactory,
    ) {
        $this->faker = Factory::create('fr_FR');
    }

    public function load(ObjectManager $manager): void
    {
        $proxy = $this->createProxy(
            null,
            $rounds = [$this->getReference('procuration-v2-europeennes-2024-round-1', Round::class)],
            'john.durand@test.dev',
            Genders::MALE,
            'John, Patrick',
            'Durand',
            '1992-03-14',
            '+33611223344',
            'FR',
            '06000',
            'Nice',
            '57 Boulevard de la Madeleine',
            false,
            LoadGeoZoneData::getZone($manager, 'zone_city_06088'),
            $this->getReference('zone_vote_place_nice_1', Zone::class)
        );
        $proxy->adherent = $this->getReference('president-ad-1', Adherent::class);
        $manager->persist($proxy);

        $manager->persist($this->createProxy(
            null,
            $rounds,
            'jane.martin@test.dev',
            Genders::FEMALE,
            'Jane, Janine',
            'Durand',
            '1991-03-14',
            null,
            'CH',
            '8057',
            'Kilchberg',
            '12 Pilgerweg',
            false,
            LoadGeoZoneData::getZone($manager, 'zone_country_CH'),
            null,
            'BDV CH 1'
        ));

        $zone = LoadGeoZoneData::getZone($manager, 'zone_city_92024');
        for ($i = 1; $i <= 10; ++$i) {
            $manager->persist($proxy = $this->createProxy(
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
                $zone
            ));
            $proxy->setCreatedAt($date = new \DateTime('-'.$i.' days'));
            $proxy->setUpdatedAt($date);
        }

        $manager->persist($proxy = $this->createProxy(
            Uuid::fromString(self::UUID_PROXY_1),
            [$this->getReference('procuration-v2-legislatives-2024-round-1', Round::class)],
            'john.durand@test.dev',
            Genders::MALE,
            'John, Patrick',
            'Durand',
            '1992-03-14',
            '+33611223344',
            'FR',
            '06000',
            'Nice',
            '57 Boulevard de la Madeleine',
            false,
            LoadGeoZoneData::getZone($manager, 'zone_city_92024'),
            $this->getReference('zone_vote_place_clichy_1', Zone::class)
        ));
        $this->setReference('proxy_slot_1', $proxy->proxySlots->first());

        $manager->persist($this->createProxy(
            null,
            [
                $round1 = $this->getReference('procuration-v2-legislatives-2024-round-1', Round::class),
                $this->getReference('procuration-v2-legislatives-2024-round-2', Round::class),
            ],
            'pierre.durand@test.dev',
            Genders::MALE,
            'Pierre',
            'Durand',
            '1992-03-14',
            '+33611223344',
            'FR',
            '06000',
            'Nice',
            '57 Boulevard de la Madeleine',
            false,
            LoadGeoZoneData::getZone($manager, 'zone_city_92024'),
            $this->getReference('zone_vote_place_clichy_1', Zone::class),
            null,
            false,
            '123456789',
            1,
            [
                $round1->name => self::UUID_PROXY_SLOT_1,
            ]
        ));

        $manager->persist($this->createProxy(
            Uuid::fromString(self::UUID_PROXY_2),
            [$this->getReference('procuration-v2-legislatives-2024-round-2', Round::class)],
            'jacques.durand@test.dev',
            Genders::MALE,
            'Jacques, Charles',
            'Durand',
            '1992-03-14',
            '+33611223344',
            'FR',
            '06000',
            'Nice',
            '57 Boulevard de la Madeleine',
            false,
            LoadGeoZoneData::getZone($manager, 'zone_city_92024'),
            $this->getReference('zone_vote_place_clichy_1', Zone::class)
        ));

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            LoadGeoZoneData::class,
            LoadProcurationV2ElectionData::class,
            LoadAdherentData::class,
        ];
    }

    private function createProxy(
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
        bool $joinNewsletter = false,
        string $electorNumber = '123456789',
        int $slots = 1,
        array $slotsUuidMapping = [],
    ): Proxy {
        $proxy = new Proxy(
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
            null,
            $joinNewsletter
        );

        $proxy->electorNumber = $electorNumber;
        $proxy->slots = $slots;
        $proxy->refreshZoneIds();

        foreach ($rounds as $round) {
            for ($i = 1; $i <= $slots; ++$i) {
                $uuid = \array_key_exists($round->name, $slotsUuidMapping)
                    ? Uuid::fromString($slotsUuidMapping[$round->name])
                    : null;

                $proxy->proxySlots->add(new ProxySlot($round, $proxy, $uuid));
            }
        }

        return $proxy;
    }
}
