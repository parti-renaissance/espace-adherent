<?php

namespace App\DataFixtures\ORM;

use App\Entity\Adherent;
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

class LoadGeneralConventionData extends Fixture implements DependentFixtureInterface
{
    private Generator $faker;

    public function __construct()
    {
        $this->faker = Factory::create('fr_FR');
    }

    public function load(ObjectManager $manager): void
    {
        $manager->persist($this->createGeneralConvention(
            LoadGeoZoneData::getZoneReference($manager, 'zone_department_92'),
            OrganizerEnum::ASSEMBLY,
            MeetingTypeEnum::ON_SITE,
            ParticipantQuality::ADHERENT,
            $this->getReference('adherent-3', Adherent::class),
            new \DateTime('now'),
        ));

        $manager->persist($this->createGeneralConvention(
            LoadGeoZoneData::getZoneReference($manager, 'zone_department_92'),
            OrganizerEnum::COMMITTEE,
            MeetingTypeEnum::REMOTE,
            ParticipantQuality::SYMPATHIZER,
            $this->getReference('adherent-3', Adherent::class),
            new \DateTime('now'),
            LoadGeoZoneData::getZoneReference($manager, 'zone_city_92024'),
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
        Zone $departmentZone,
        OrganizerEnum $organizer,
        MeetingTypeEnum $meetingType,
        ParticipantQuality $participantQuality,
        Adherent $reporter,
        \DateTimeInterface $reportedAt,
        ?Zone $committeeZone = null,
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
        $generalConvention = new GeneralConvention();

        $generalConvention->departmentZone = $departmentZone;
        $generalConvention->organizer = $organizer;
        $generalConvention->meetingType = $meetingType;
        $generalConvention->participantQuality = $participantQuality;
        $generalConvention->committeeZone = $committeeZone;
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
