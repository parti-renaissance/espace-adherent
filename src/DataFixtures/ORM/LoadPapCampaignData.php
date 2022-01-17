<?php

namespace App\DataFixtures\ORM;

use App\Entity\Geo\Zone;
use App\Entity\Jecoute\Survey;
use App\Entity\Pap\Campaign;
use Cake\Chronos\Chronos;
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

    public function load(ObjectManager $manager)
    {
        $nationalSurvey1 = $this->getReference('national-survey-1');
        $nationalSurvey2 = $this->getReference('national-survey-2');
        $nationalSurvey3 = $this->getReference('national-survey-3');

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
            LoadGeoZoneData::getZoneReference($manager, 'zone_department_92')
        );
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
            LoadGeoZoneData::getZoneReference($manager, 'zone_city_59350')
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
            LoadGeoZoneData::getZoneReference($manager, 'zone_city_06088')
        );

        $manager->persist($campaign1);
        $manager->persist($campaign2);
        $manager->persist($campaign3);
        $manager->persist($campaign4);
        $manager->persist($campaignFinished);
        $manager->persist($campaign92);
        $manager->persist($campaign59350);
        $manager->persist($campaign06088);

        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            LoadAdminData::class,
            LoadJecouteSurveyData::class,
            LoadGeoZoneData::class,
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
        Zone $zone = null
    ): Campaign {
        return new Campaign(
            Uuid::fromString($uuid),
            $title,
            $brief,
            $survey,
            $goal,
            new Chronos($beginAt),
            new Chronos($finishAt),
            $nbAddresses,
            $nbVoters,
            $zone
        );
    }
}
