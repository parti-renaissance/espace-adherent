<?php

declare(strict_types=1);

namespace App\DataFixtures\ORM;

use App\Entity\Adherent;
use App\Entity\Jecoute\NationalSurvey;
use App\Entity\Jecoute\Survey;
use App\Entity\Pap\Campaign;
use App\Entity\Pap\VotePlace;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Ramsey\Uuid\Uuid;

class LoadPapCampaignData extends Fixture implements DependentFixtureInterface
{
    public const CAMPAIGN_1_UUID = 'd0fa7f9c-e976-44ad-8a52-2a0a0d8acaf9';
    public const CAMPAIGN_2_UUID = '1c67b6bd-6da9-4a72-a266-813c419e7024';
    public const CAMPAIGN_3_UUID = '63460047-c81a-44b9-aec9-152ecf58df93';
    public const CAMPAIGN_4_UUID = '932d67d1-2da6-4695-82f6-42afc20f2e41';
    public const CAMPAIGN_5_UUID = '9ba6b743-5018-4358-bdc0-eb2094010beb';
    public const CAMPAIGN_6_UUID = 'e3c6e83f-7471-4e8f-b348-6c2eb26723ce';
    public const CAMPAIGN_7_UUID = '31f24b6c-0884-461a-af34-dbbb7b1276ab';
    public const CAMPAIGN_8_UUID = '74a0d169-1e10-4159-a399-bf499706a2c6';
    public const CAMPAIGN_9_UUID = '8fbee663-4f18-49d4-9c2d-4553bcc859cf';
    public const CAMPAIGN_10_UUID = '08463014-bbfe-421c-b8fb-5e456414b088';
    public const CAMPAIGN_11_UUID = 'd572fb7b-4c60-451a-9303-84f45c60a490';
    public const CAMPAIGN_12_UUID = 'd65b621c-43fb-42e7-a169-6f79c44a31bc';
    public const CAMPAIGN_13_UUID = '91ecd823-0e31-4aa1-880b-1cbbcd262762';
    public const CAMPAIGN_14_UUID = '115efbe5-af28-419a-a0a5-9f61c5d9f527';

    public function load(ObjectManager $manager): void
    {
        $nationalSurvey1 = $this->getReference('national-survey-1', NationalSurvey::class);
        $nationalSurvey2 = $this->getReference('national-survey-2', NationalSurvey::class);
        $nationalSurvey3 = $this->getReference('national-survey-3', NationalSurvey::class);

        $campaign1 = $this->createCampaign(
            self::CAMPAIGN_1_UUID,
            'Campagne de 10 jours suivants',
            '**Campagne** de 10 jours suivants',
            $nationalSurvey3,
            600,
            '-1 hour',
            '+10 days',
            4,
            7
        );
        $campaign1->addVotePlace($this->getReference('pap-vote-place--paris-8-a', VotePlace::class));
        $campaign1->addVotePlace($this->getReference('pap-vote-place--paris-8-b', VotePlace::class));
        $campaign1->addVotePlace($this->getReference('pap-vote-place--paris-3-b', VotePlace::class));
        $campaign1->addVotePlace($this->getReference('pap-vote-place--anthony-a', VotePlace::class));
        $campaign1->addVotePlace($this->getReference('pap-vote-place--anthony-b', VotePlace::class));
        $campaign1->addVotePlace($this->getReference('pap-vote-place--sartrouville-a', VotePlace::class));
        $campaign1->addVotePlace($this->getReference('pap-vote-place--sartrouville-b', VotePlace::class));

        $this->addReference('pap-campaign-1', $campaign1);

        $campaign2 = $this->createCampaign(
            self::CAMPAIGN_2_UUID,
            'Campagne de 5 jours suivants',
            '**Campagne** de 5 jours suivants',
            $nationalSurvey2,
            500,
            '-1 hour',
            '+5 days',
            4,
            7
        );
        $this->addReference('pap-campaign-2', $campaign2);

        $campaign3 = $this->createCampaign(
            self::CAMPAIGN_3_UUID,
            'Campagne dans 10 jours',
            '### Campagne dans 10 jours',
            $nationalSurvey2,
            400,
            '+10 days',
            '+20 days',
            4,
            7
        );
        $this->addReference('pap-campaign-3', $campaign3);

        $campaign4 = $this->createCampaign(
            self::CAMPAIGN_4_UUID,
            'Campagne dans 20 jours',
            '### Campagne dans 20 jours',
            $nationalSurvey2,
            400,
            '+20 days',
            '+30 days',
            4,
            7
        );
        $this->addReference('pap-campaign-4', $campaign4);

        $campaignFinished = $this->createCampaign(
            self::CAMPAIGN_5_UUID,
            'Campagne terminée',
            null,
            $nationalSurvey1,
            100,
            '2021-11-01',
            '2021-11-12',
            4,
            7
        );
        $this->addReference('pap-campaign-finished', $campaignFinished);

        $campaign92 = $this->createCampaign(
            self::CAMPAIGN_6_UUID,
            'Campagne locale du département 92',
            null,
            $nationalSurvey1,
            100,
            '+10 days',
            '+20 days',
            1,
            0,
            [LoadGeoZoneData::getZoneReference($manager, 'zone_department_92')],
            $this->getReference('adherent-3', Adherent::class),
        );
        $campaign92->addVotePlace($this->getReference('pap-vote-place--anthony-c', VotePlace::class));
        $this->addReference('pap-campaign-92', $campaign92);

        $campaign59350 = $this->createCampaign(
            self::CAMPAIGN_7_UUID,
            'Campagne locale de la ville de Lille (59350)',
            null,
            $nationalSurvey1,
            100,
            '+10 days',
            '+20 days',
            0,
            0,
            [LoadGeoZoneData::getZoneReference($manager, 'zone_city_59350')],
            $this->getReference('adherent-8', Adherent::class),
        );

        $campaign06088 = $this->createCampaign(
            self::CAMPAIGN_8_UUID,
            'Campagne locale de la ville de Nice (06088)',
            null,
            $nationalSurvey1,
            100,
            '+10 days',
            '+20 days',
            0,
            0,
            [LoadGeoZoneData::getZoneReference($manager, 'zone_city_06088')]
        );
        $campaign06088->addVotePlace($this->getReference('pap-vote-place--nice-a', VotePlace::class));

        $campaign75_08 = $this->createCampaign(
            self::CAMPAIGN_9_UUID,
            'Campagne locale de Paris 8ème',
            null,
            $nationalSurvey1,
            100,
            '-1 day',
            '+30 days',
            1,
            0,
            [LoadGeoZoneData::getZoneReference($manager, 'zone_district_75-1')],
            $this->getReference('senatorial-candidate', Adherent::class),
        );
        $campaign75_08->addVotePlace($this->getReference('pap-vote-place--paris-8-d', VotePlace::class));
        $this->addReference('pap-campaign-75-08', $campaign75_08);

        $campaign75_08_r = $this->createCampaign(
            self::CAMPAIGN_10_UUID,
            'Campagne locale de Paris 8ème avec des portes frappées',
            null,
            $nationalSurvey1,
            100,
            '-1 day',
            '+30 days',
            1,
            0,
            [LoadGeoZoneData::getZoneReference($manager, 'zone_district_75-1')],
            $this->getReference('senatorial-candidate', Adherent::class),
        );
        $campaign75_08_r->addVotePlace($this->getReference('pap-vote-place--paris-8-e', VotePlace::class));
        $this->addReference('pap-campaign-75-08-r', $campaign75_08_r);

        $campaign75_08_upcoming = $this->createCampaign(
            self::CAMPAIGN_11_UUID,
            'Campagne locale de Paris 8ème dans 2 mois',
            null,
            $nationalSurvey1,
            100,
            '+2 months',
            '+3 months',
            1,
            0,
            [LoadGeoZoneData::getZoneReference($manager, 'zone_district_75-1')],
            $this->getReference('senatorial-candidate', Adherent::class),
        );
        $campaign75_08_upcoming->addVotePlace($this->getReference('pap-vote-place--paris-8-e', VotePlace::class));
        $this->addReference('pap-campaign-75-08-upcoming', $campaign75_08_upcoming);

        $campaignNationalUpcoming = $this->createCampaign(
            self::CAMPAIGN_12_UUID,
            'Campagne dans un mois',
            '### Campagne dans un mois',
            $nationalSurvey2,
            400,
            '+1 month',
            '+2 months',
            4,
            7,
            [],
            $this->getReference('deputy-75-1', Adherent::class)
        );
        $this->addReference('pap-campaign-national-upcoming', $campaignNationalUpcoming);

        $campaign75_08_disabled = $this->createCampaign(
            self::CAMPAIGN_13_UUID,
            'Campagne locale désactivée de Paris 8ème avec des portes frappées',
            null,
            $nationalSurvey1,
            100,
            '-1 day',
            '+30 days',
            1,
            0,
            [LoadGeoZoneData::getZoneReference($manager, 'zone_district_75-1')],
            $this->getReference('senatorial-candidate', Adherent::class),
            false
        );
        $this->addReference('pap-campaign-75-08-disabled', $campaign75_08_disabled);

        $campaignNationalDisabled = $this->createCampaign(
            self::CAMPAIGN_14_UUID,
            'Campagne désactivée de 10 jours suivants',
            '**Campagne désactivée** de 10 jours suivants',
            $nationalSurvey3,
            150,
            '-1 hour',
            '+10 days',
            4,
            7,
            [],
            null,
            false
        );
        $this->addReference('pap-campaign-national-disabled', $campaignNationalDisabled);

        $manager->persist($campaign1);
        $manager->persist($campaign2);
        $manager->persist($campaign3);
        $manager->persist($campaign4);
        $manager->persist($campaignFinished);
        $manager->persist($campaign92);
        $manager->persist($campaign59350);
        $manager->persist($campaign06088);
        $manager->persist($campaign75_08);
        $manager->persist($campaign75_08_r);
        $manager->persist($campaign75_08_upcoming);
        $manager->persist($campaignNationalUpcoming);
        $manager->persist($campaign75_08_disabled);
        $manager->persist($campaignNationalDisabled);

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            LoadAdminData::class,
            LoadJecouteSurveyData::class,
            LoadGeoZoneData::class,
            LoadPapVotePlaceData::class,
        ];
    }

    private function createCampaign(
        string $uuid,
        string $title,
        ?string $brief,
        Survey $survey,
        int $goal,
        string $beginAt,
        string $finishAt,
        int $nbAddresses = 0,
        int $nbVoters = 0,
        array $zones = [],
        ?Adherent $createdBy = null,
        bool $enabled = true,
    ): Campaign {
        return new Campaign(
            Uuid::fromString($uuid),
            $title,
            $brief,
            $survey,
            $goal,
            new \DateTime($beginAt),
            new \DateTime($finishAt),
            $nbAddresses,
            $nbVoters,
            $zones,
            $createdBy,
            $enabled
        );
    }
}
