<?php

namespace App\DataFixtures\ORM;

use App\Entity\Adherent;
use App\Entity\AdherentMessage\Filter\AudienceFilter;
use App\Entity\AdherentMessage\Segment\AudienceSegment;
use App\Entity\Geo\Zone;
use App\ValueObject\Genders;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Ramsey\Uuid\Uuid;

class LoadAudienceSegmentData extends Fixture implements DependentFixtureInterface
{
    public const SEGMENT_1_UUID = '830d230f-67fb-4217-9986-1a3ed7d3d5e7';
    public const SEGMENT_2_UUID = 'f6c36dd7-0517-4caf-ba6f-ec6822f2ec12';

    public function load(ObjectManager $manager)
    {
        $segment1 = $this->createSegment(
            self::SEGMENT_1_UUID,
            $this->getReference('adherent-8'),
            LoadGeoZoneData::getZoneReference($manager, 'zone_district_75-1'),
            'Maria',
            'Dupont',
            Genders::FEMALE,
            20,
            42,
            new \DateTime('2017-08-03'),
            new \DateTime('2021-07-11'),
            true,
            false,
            true
        );
        $segment2 = $this->createSegment(
            self::SEGMENT_2_UUID,
            $this->getReference('deputy-75-1'),
            LoadGeoZoneData::getZoneReference($manager, 'zone_city_92024'),
            null,
            null,
            Genders::MALE,
            20,
            30,
            null,
            null,
            true,
            false,
            false
        );

        $manager->persist($segment1);
        $manager->persist($segment2);

        $manager->flush();
    }

    public function createSegment(
        string $uuid,
        Adherent $author,
        Zone $zone,
        string $firstName = null,
        string $lastName = null,
        string $gender = null,
        int $ageMin = null,
        int $ageMax = null,
        \DateTime $registeredSince = null,
        \DateTime $registeredUntil = null,
        bool $includeAdherentsNoCommittee = null,
        bool $includeAdherentsInCommittee = null,
        bool $isCertified = null
    ): AudienceSegment {
        $segment = new AudienceSegment(Uuid::fromString($uuid));
        $segment->setAuthor($author);
        $filter = new AudienceFilter();
        $filter->setZone($zone);
        $filter->setFirstName($firstName);
        $filter->setLastName($lastName);
        $filter->setGender($gender);
        $filter->setAgeMin($ageMin);
        $filter->setAgeMax($ageMax);
        $filter->setRegisteredSince($registeredSince);
        $filter->setRegisteredUntil($registeredUntil);
        $filter->setZone($zone);
        $filter->setIncludeAdherentsNoCommittee(!$includeAdherentsNoCommittee);
        $filter->setIncludeAdherentsInCommittee($includeAdherentsInCommittee);
        $filter->setIsCertified($isCertified);
        $segment->setFilter($filter);

        return $segment;
    }

    public function getDependencies()
    {
        return [
            LoadAdminData::class,
            LoadAdherentData::class,
        ];
    }
}
