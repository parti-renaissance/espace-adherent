<?php

namespace App\DataFixtures\ORM;

use App\Address\PostAddressFactory;
use App\Entity\Geo\Zone;
use App\Entity\ProcurationV2\Proxy;
use App\Entity\ProcurationV2\ProxySlot;
use App\Utils\PhoneNumberUtils;
use App\ValueObject\Genders;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Faker\Generator;
use Ramsey\Uuid\Uuid;

class LoadProcurationV2ProxyData extends Fixture implements DependentFixtureInterface
{
    public const UUID_PROXY_SLOT_1 = 'b024ff2a-c74b-442c-8339-7df9d0c104b6';

    private Generator $faker;

    public function __construct(
        private readonly PostAddressFactory $addressFactory
    ) {
        $this->faker = Factory::create('fr_FR');
    }

    public function load(ObjectManager $manager)
    {
        $proxy = $this->createProxy(
            $rounds = [$this->getReference('procuration-v2-europeennes-2024-round-1')],
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
            $this->getReference('zone_vote_place_nice_1')
        );
        $proxy->adherent = $this->getReference('president-ad-1');
        $manager->persist($proxy);

        $manager->persist($this->createProxy(
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
            [$this->getReference('procuration-v2-legislatives-2024-round-1')],
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
            $this->getReference('zone_vote_place_clichy_1')
        ));
        $this->setReference('proxy_slot_1', $proxy->proxySlots->first());

        $manager->persist($this->createProxy(
            [
                $round1 = $this->getReference('procuration-v2-legislatives-2024-round-1'),
                $this->getReference('procuration-v2-legislatives-2024-round-2'),
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
            $this->getReference('zone_vote_place_clichy_1'),
            null,
            false,
            '123456789',
            1,
            [
                $round1->name => self::UUID_PROXY_SLOT_1,
            ]
        ));

        $manager->persist($this->createProxy(
            [$this->getReference('procuration-v2-legislatives-2024-round-2')],
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
            $this->getReference('zone_vote_place_clichy_1')
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
        array $slotsUuidMapping = []
    ): Proxy {
        $proxy = new Proxy(
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
