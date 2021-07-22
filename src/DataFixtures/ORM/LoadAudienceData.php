<?php

namespace App\DataFixtures\ORM;

use App\Entity\Audience\AbstractAudience;
use App\Entity\Audience\DeputyAudience;
use App\Entity\Audience\ReferentAudience;
use App\Entity\Geo\Zone;
use App\ValueObject\Genders;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Ramsey\Uuid\Uuid;

class LoadAudienceData extends Fixture implements DependentFixtureInterface
{
    public const AUDIENCE_1_UUID = '4fe24658-000c-4223-87be-d09129c1e191';
    public const AUDIENCE_2_UUID = 'f7ac8140-0a5b-4832-a5f4-47e661dc130c';

    public function load(ObjectManager $manager)
    {
        $audienceReferentOnlyRequired = $this->createAudience(
            ReferentAudience::class,
            self::AUDIENCE_1_UUID,
            'Avec les paramètres nécessaires',
            LoadGeoZoneData::getZoneReference($manager, 'zone_department_77')
        );
        $audienceAll = $this->createAudience(
            DeputyAudience::class,
            self::AUDIENCE_2_UUID,
            'Avec tous les paramètres possibles',
            LoadGeoZoneData::getZoneReference($manager, 'zone_city_75056'),
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

        $manager->persist($audienceReferentOnlyRequired);
        $manager->persist($audienceAll);

        $manager->flush();
    }

    public function createAudience(
        string $classAudience,
        string $uuid,
        string $name,
        Zone $zone,
        string $firstName = null,
        string $lastName = null,
        string $gender = null,
        int $ageMin = null,
        int $ageMax = null,
        \DateTime $registeredSince = null,
        \DateTime $registeredUntil = null,
        bool $isCommitteeMember = null,
        bool $isCertified = null,
        bool $hasEmailSubscription = null,
        bool $hasSmsSubscription = null
    ): AbstractAudience {
        $audience = new $classAudience(Uuid::fromString($uuid));
        $audience->setName($name);
        $audience->setFirstName($firstName);
        $audience->setLastName($lastName);
        $audience->setGender($gender);
        $audience->setAgeMin($ageMin);
        $audience->setAgeMax($ageMax);
        $audience->setRegisteredSince($registeredSince);
        $audience->setRegisteredUntil($registeredUntil);
        $audience->setZone($zone);
        $audience->setIsCommitteeMember($isCommitteeMember);
        $audience->setIsCertified($isCertified);
        $audience->setHasEmailSubscription($hasEmailSubscription);
        $audience->setHasSmsSubscription($hasSmsSubscription);

        return $audience;
    }

    public function getDependencies()
    {
        return [
            LoadGeoZoneData::class,
        ];
    }
}
