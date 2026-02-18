<?php

declare(strict_types=1);

namespace App\DataFixtures\ORM;

use App\Entity\Adherent;
use App\Entity\AdherentMessage\AdherentMessageFilter;
use App\Entity\AdherentMessage\Segment\AudienceSegment;
use App\Entity\Geo\Zone;
use App\Scope\ScopeEnum;
use App\ValueObject\Genders;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Ramsey\Uuid\Uuid;

class LoadAudienceSegmentData extends Fixture implements DependentFixtureInterface
{
    public const SEGMENT_2_UUID = 'f6c36dd7-0517-4caf-ba6f-ec6822f2ec12';

    public function load(ObjectManager $manager): void
    {
        $segment2 = $this->createSegment(
            self::SEGMENT_2_UUID,
            $this->getReference('deputy-75-1', Adherent::class),
            ScopeEnum::DEPUTY,
            LoadGeoZoneData::getZoneReference($manager, 'zone_city_92024'),
            null,
            null,
            Genders::MALE,
            20,
            30,
            null,
            null,
            false,
            false
        );

        $manager->persist($segment2);

        $manager->flush();
    }

    public function createSegment(
        string $uuid,
        Adherent $author,
        string $scope,
        Zone $zone,
        ?string $firstName = null,
        ?string $lastName = null,
        ?string $gender = null,
        ?int $ageMin = null,
        ?int $ageMax = null,
        ?\DateTime $registeredSince = null,
        ?\DateTime $registeredUntil = null,
        ?bool $includeAdherentsInCommittee = null,
        ?bool $isCertified = null,
    ): AudienceSegment {
        $segment = new AudienceSegment(Uuid::fromString($uuid));
        $segment->setAuthor($author);
        $filter = new AdherentMessageFilter();
        $filter->setScope($scope);
        $filter->setZone($zone);
        $filter->setFirstName($firstName);
        $filter->setLastName($lastName);
        $filter->setGender($gender);
        $filter->setAgeMin($ageMin);
        $filter->setAgeMax($ageMax);
        $filter->setRegisteredSince($registeredSince);
        $filter->setRegisteredUntil($registeredUntil);
        $filter->setZone($zone);
        $filter->setIsCommitteeMember($includeAdherentsInCommittee);
        $filter->setIsCertified($isCertified);
        $segment->setFilter($filter);

        return $segment;
    }

    public function getDependencies(): array
    {
        return [
            LoadAdminData::class,
            LoadAdherentData::class,
        ];
    }
}
