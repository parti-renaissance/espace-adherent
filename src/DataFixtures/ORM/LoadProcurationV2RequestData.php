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

class LoadProcurationV2RequestData extends Fixture implements DependentFixtureInterface
{
    public function __construct(
        private readonly PostAddressFactory $addressFactory
    ) {
    }

    public function load(ObjectManager $manager)
    {
        $request1 = $this->createRequest(
            $this->getReference('procuration-v2-round-1'),
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
        );

        $manager->persist($request1);

        $request2 = $this->createRequest(
            $this->getReference('procuration-v2-round-1'),
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
            'BDV CH 1'
        );

        $manager->persist($request2);

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
        ?string $customVotePlace = null
    ): Request {
        return new Request(
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
    }
}
