<?php

namespace App\DataFixtures\ORM;

use App\Address\PostAddressFactory;
use App\Entity\Geo\Zone;
use App\Entity\ProcurationV2\Proxy;
use App\Entity\ProcurationV2\Round;
use App\Utils\PhoneNumberUtils;
use App\ValueObject\Genders;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class LoadProcurationV2ProxyData extends Fixture implements DependentFixtureInterface
{
    public function __construct(
        private readonly PostAddressFactory $addressFactory
    ) {
    }

    public function load(ObjectManager $manager)
    {
        $proxy1 = $this->createProxy(
            $this->getReference('procuration-v2-round-1'),
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
            LoadGeoZoneData::getZoneReference($manager, 'zone_city_06088'),
            $this->getReference('zone_vote_place_nice_1')
        );

        $manager->persist($proxy1);

        $proxy2 = $this->createProxy(
            $this->getReference('procuration-v2-round-1'),
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
            LoadGeoZoneData::getZoneReference($manager, 'zone_country_CH'),
            null,
            'BDV CH 1'
        );

        $manager->persist($proxy2);

        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            LoadGeoZoneData::class,
            LoadProcurationV2ElectionData::class,
        ];
    }

    private function createProxy(
        Round $round,
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
        string $electorNumber = '123456789',
        int $slots = 1
    ): Proxy {
        $proxy = new Proxy(
            $round,
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
            $customVotePlace
        );

        $proxy->electorNumber = $electorNumber;
        $proxy->slots = $slots;

        return $proxy;
    }
}
