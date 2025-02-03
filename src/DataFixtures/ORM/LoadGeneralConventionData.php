<?php

namespace App\DataFixtures\ORM;

use App\Entity\Adherent;
use App\Entity\Committee;
use App\Entity\GeneralConvention\GeneralConvention;
use App\Entity\Geo\Zone;
use App\GeneralConvention\MeetingTypeEnum;
use App\GeneralConvention\OrganizerEnum;
use App\GeneralConvention\ParticipantQuality;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Faker\Generator;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

class LoadGeneralConventionData extends Fixture implements DependentFixtureInterface
{
    private const UUID_1 = 'c5317499-7130-4255-a7f8-418e72f5dfa5';
    private const UUID_2 = 'b3a2b082-01fc-4306-9fdb-6559ebe765b1';
    private const UUID_3 = '54c9ae4c-3e2d-475d-8993-54639ec58ea1';

    private Generator $faker;

    public function __construct()
    {
        $this->faker = Factory::create('fr_FR');
    }

    public function load(ObjectManager $manager): void
    {
        $manager->persist($this->createGeneralConvention(
            Uuid::fromString(self::UUID_1),
            LoadGeoZoneData::getZoneReference($manager, 'zone_department_92'),
            OrganizerEnum::ASSEMBLY,
            MeetingTypeEnum::ON_SITE,
            ParticipantQuality::ADHERENT,
            $this->getReference('adherent-3', Adherent::class),
            new \DateTime('now'),
        ));

        $manager->persist($this->createGeneralConvention(
            Uuid::fromString(self::UUID_2),
            LoadGeoZoneData::getZoneReference($manager, 'zone_department_92'),
            OrganizerEnum::COMMITTEE,
            MeetingTypeEnum::REMOTE,
            ParticipantQuality::SYMPATHIZER,
            $this->getReference('adherent-3', Adherent::class),
            new \DateTime('now'),
            $this->getReference('committee-v2-1', Committee::class),
            null,
            20,
            $this->faker->text(),
            $this->faker->text(),
            $this->faker->text(),
            $this->faker->text(),
            $this->faker->text(),
            $this->faker->text(),
            $this->faker->text(),
            $this->faker->text(),
            $this->faker->text(),
            $this->faker->text(),
            $this->faker->text(),
            $this->faker->text(),
            $this->faker->text(),
            $this->faker->text(),
            $this->faker->text(),
        ));

        $manager->persist($this->createGeneralConvention(
            Uuid::fromString(self::UUID_3),
            LoadGeoZoneData::getZoneReference($manager, 'zone_department_92'),
            OrganizerEnum::DISTRICT,
            MeetingTypeEnum::REMOTE,
            ParticipantQuality::ADHERENT_BEFORE,
            $this->getReference('adherent-3', Adherent::class),
            new \DateTime('now'),
            null,
            LoadGeoZoneData::getZoneReference($manager, 'zone_district_92-4'),
            10,
            $this->faker->text(),
            $this->faker->text(),
            $this->faker->text(),
            $this->faker->text(),
            $this->faker->text(),
            $this->faker->text(),
            $this->faker->text(),
            $this->faker->text(),
            $this->faker->text(),
            $this->faker->text(),
            $this->faker->text(),
            $this->faker->text(),
            $this->faker->text(),
            $this->faker->text(),
            $this->faker->text(),
        ));

        $manager->flush();
    }

    private function createGeneralConvention(
        UuidInterface $uuid,
        Zone $departmentZone,
        OrganizerEnum $organizer,
        MeetingTypeEnum $meetingType,
        ParticipantQuality $participantQuality,
        Adherent $reporter,
        \DateTimeInterface $reportedAt,
        ?Committee $committee = null,
        ?Zone $districtZone = null,
        int $membersCount = 0,
        ?string $generalSummary = null,
        ?string $partyDefinitionSummary = null,
        ?string $uniquePartySummary = null,
        ?string $progressSince2016 = null,
        ?string $partyObjectives = null,
        ?string $governance = null,
        ?string $communication = null,
        ?string $militantTraining = null,
        ?string $memberJourney = null,
        ?string $mobilization = null,
        ?string $talentDetection = null,
        ?string $electionPreparation = null,
        ?string $relationshipWithSupporters = null,
        ?string $workWithPartners = null,
        ?string $additionalComments = null,
    ): GeneralConvention {
        $generalConvention = new GeneralConvention($uuid);

        $generalConvention->departmentZone = $departmentZone;
        $generalConvention->organizer = $organizer;
        $generalConvention->meetingType = $meetingType;
        $generalConvention->participantQuality = $participantQuality;
        $generalConvention->committee = $committee;
        $generalConvention->districtZone = $districtZone;
        $generalConvention->reporter = $reporter;
        $generalConvention->reportedAt = $reportedAt;
        $generalConvention->membersCount = $membersCount;
        $generalConvention->generalSummary = $generalSummary;
        $generalConvention->partyDefinitionSummary = $partyDefinitionSummary;
        $generalConvention->uniquePartySummary = $uniquePartySummary;
        $generalConvention->progressSince2016 = $progressSince2016;
        $generalConvention->partyObjectives = $partyObjectives;
        $generalConvention->governance = $governance;
        $generalConvention->communication = $communication;
        $generalConvention->militantTraining = $militantTraining;
        $generalConvention->memberJourney = $memberJourney;
        $generalConvention->mobilization = $mobilization;
        $generalConvention->talentDetection = $talentDetection;
        $generalConvention->electionPreparation = $electionPreparation;
        $generalConvention->relationshipWithSupporters = $relationshipWithSupporters;
        $generalConvention->workWithPartners = $workWithPartners;
        $generalConvention->additionalComments = $additionalComments;

        return $generalConvention;
    }

    public function getDependencies(): array
    {
        return [
            LoadAdherentData::class,
        ];
    }
}
