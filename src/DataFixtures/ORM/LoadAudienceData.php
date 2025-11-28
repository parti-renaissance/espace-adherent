<?php

declare(strict_types=1);

namespace App\DataFixtures\ORM;

use App\Entity\Audience\Audience;
use App\Entity\Geo\Zone;
use App\Scope\ScopeEnum;
use App\ValueObject\Genders;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Ramsey\Uuid\Uuid;

class LoadAudienceData extends Fixture implements DependentFixtureInterface
{
    public const AUDIENCE_1_UUID = '4fe24658-000c-4223-87be-d09129c1e191';
    public const AUDIENCE_2_UUID = 'bd298079-f763-4c7a-9a8a-a243d01d0e31';
    public const AUDIENCE_3_UUID = '174e6333-e7b9-49dd-92e7-0663b9e0f165';
    public const AUDIENCE_4_UUID = '6e1a9be9-254d-48f7-a0ab-c26f35cfa783';
    public const AUDIENCE_5_UUID = '4562e1ca-09ee-4500-96b6-a73431e40bf1';
    public const AUDIENCE_6_UUID = 'ed148588-366a-4486-9034-c4b5d439681e';
    public const AUDIENCE_7_UUID = 'f7ac8140-0a5b-4832-a5f4-47e661dc130c';
    public const AUDIENCE_8_UUID = 'a11d388e-0699-4296-94b1-eb97ab0ff0f7';
    public const AUDIENCE_9_UUID = 'aef77422-0797-40fd-a160-2c5f2ee19260';
    public const AUDIENCE_10_UUID = '79b2046c-1722-406e-b5b4-4ddcc0827ead';

    public function load(ObjectManager $manager): void
    {
        $audienceDeputyOnlyRequired = $this->createAudience(
            self::AUDIENCE_2_UUID,
            ScopeEnum::DEPUTY,
            'Audience DEPUTY avec les paramètres nécessaires',
            LoadGeoZoneData::getZoneReference($manager, 'zone_district_75-1')
        );
        $audienceSenatorOnlyRequired = $this->createAudience(
            self::AUDIENCE_3_UUID,
            ScopeEnum::SENATOR,
            'Audience SENATOR avec les paramètres nécessaires',
            LoadGeoZoneData::getZoneReference($manager, 'zone_department_59')
        );
        $audienceCandidateOnlyRequired = $this->createAudience(
            self::AUDIENCE_4_UUID,
            ScopeEnum::CANDIDATE,
            'Audience CANDIDATE avec les paramètres nécessaires',
            LoadGeoZoneData::getZoneReference($manager, 'zone_department_77')
        );
        $audienceDeputyAll = $this->createAudience(
            self::AUDIENCE_7_UUID,
            ScopeEnum::DEPUTY,
            'Audience DEPUTY avec tous les paramètres possibles',
            LoadGeoZoneData::getZoneReference($manager, 'zone_district_75-1'),
            'Julien',
            'PREMIER',
            Genders::MALE,
            18,
            42,
            new \DateTime('2017-07-01'),
            new \DateTime('2021-07-01'),
            true,
            false,
            true,
            false
        );
        $audienceDeputy2020 = $this->createAudience(
            self::AUDIENCE_8_UUID,
            ScopeEnum::DEPUTY,
            'Audience DEPUTY à Clichy avant 2020',
            LoadGeoZoneData::getZoneReference($manager, 'zone_city_92024'),
            null,
            null,
            null,
            null,
            null,
            new \DateTime('2019-12-31')
        );
        $audienceCandidate = $this->createAudience(
            self::AUDIENCE_9_UUID,
            ScopeEnum::CANDIDATE,
            'Audience CANDIDATE avec quelques paramètres',
            LoadGeoZoneData::getZoneReference($manager, 'zone_region_11'),
            null,
            'PICARD',
            Genders::MALE
        );
        $audienceCandidateParis = $this->createAudience(
            self::AUDIENCE_10_UUID,
            ScopeEnum::CANDIDATE,
            'Audience CANDIDATE, les hommes à Paris',
            LoadGeoZoneData::getZoneReference($manager, 'zone_city_75056'),
            null,
            null,
            Genders::MALE
        );

        $manager->persist($audienceDeputyOnlyRequired);
        $manager->persist($audienceSenatorOnlyRequired);
        $manager->persist($audienceCandidateOnlyRequired);
        $manager->persist($audienceDeputyAll);
        $manager->persist($audienceDeputy2020);
        $manager->persist($audienceCandidate);
        $manager->persist($audienceCandidateParis);

        $manager->flush();
    }

    public function createAudience(
        string $uuid,
        string $scope,
        string $name,
        Zone $zone,
        ?string $firstName = null,
        ?string $lastName = null,
        ?string $gender = null,
        ?int $ageMin = null,
        ?int $ageMax = null,
        ?\DateTime $registeredSince = null,
        ?\DateTime $registeredUntil = null,
        ?bool $isCommitteeMember = null,
        ?bool $isCertified = null,
        ?bool $hasEmailSubscription = null,
        ?bool $hasSmsSubscription = null,
    ): Audience {
        $audience = new Audience(Uuid::fromString($uuid));
        $audience->setScope($scope);
        $audience->setName($name);
        $audience->setFirstName($firstName);
        $audience->setLastName($lastName);
        $audience->setGender($gender);
        $audience->setAgeMin($ageMin);
        $audience->setAgeMax($ageMax);
        $audience->setRegisteredSince($registeredSince);
        $audience->setRegisteredUntil($registeredUntil);
        $audience->addZone($zone);
        $audience->setZone($zone);
        $audience->setIsCommitteeMember($isCommitteeMember);
        $audience->setIsCertified($isCertified);
        $audience->setHasEmailSubscription($hasEmailSubscription);
        $audience->setHasSmsSubscription($hasSmsSubscription);

        return $audience;
    }

    public function getDependencies(): array
    {
        return [
            LoadGeoZoneData::class,
        ];
    }
}
