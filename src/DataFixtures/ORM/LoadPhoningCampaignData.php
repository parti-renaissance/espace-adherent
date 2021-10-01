<?php

namespace App\DataFixtures\ORM;

use App\Entity\Audience\AudienceSnapshot;
use App\Entity\Jecoute\Survey;
use App\Entity\Phoning\Campaign;
use App\Entity\Team\Team;
use App\ValueObject\Genders;
use Cake\Chronos\Chronos;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Ramsey\Uuid\Uuid;

class LoadPhoningCampaignData extends Fixture implements DependentFixtureInterface
{
    public const CAMPAIGN_1_UUID = '4ebb184c-24d9-4aeb-bb36-afe44f294387';
    public const CAMPAIGN_2_UUID = '4d91b94c-4b39-43c7-9c88-f4be7e2fe0bc';
    public const CAMPAIGN_3_UUID = 'fdc99fb4-0492-4488-a53d-b7aa02888ffe';
    public const CAMPAIGN_4_UUID = 'b5e1b850-faec-4da7-8da6-d64b94494668';
    public const CAMPAIGN_5_UUID = 'cc8f32ce-176c-42c8-a7e9-b854cc8fc61e';
    public const CAMPAIGN_6_UUID = 'b48af58c-51e8-4f1b-a432-deace2969fda';

    public function load(ObjectManager $manager)
    {
        $team1 = $this->getReference('team-1');
        $team2 = $this->getReference('team-2');
        $nationalSurvey1 = $this->getReference('national-survey-1');
        $nationalSurvey2 = $this->getReference('national-survey-2');

        $campaign1 = $this->createCampaign(
            self::CAMPAIGN_1_UUID,
            'Campagne pour les hommes',
            '**Campagne** pour les hommes',
            $team1,
            $nationalSurvey1,
            500,
            '+10 days'
        );
        ($audience = $campaign1->getAudience())->setGender(Genders::MALE);
        $audience->addZone(LoadGeoZoneData::getZoneReference($manager, 'zone_city_75056'));

        $this->addReference('campaign-1', $campaign1);

        $campaign2 = $this->createCampaign(
            self::CAMPAIGN_2_UUID,
            'Campagne pour les femmes',
            '### Campagne pour les femmes',
            $team2,
            $nationalSurvey2,
            500,
            '+15 days'
        );
        $campaign2->getAudience()->setGender(Genders::FEMALE);
        $this->addReference('campaign-2', $campaign2);

        $campaignFinished = $this->createCampaign(
            self::CAMPAIGN_3_UUID,
            'Campagne terminé',
            null,
            $team1,
            $nationalSurvey1,
            100,
            '-5 days'
        );
        $campaignFinished->getAudience()->setGender(Genders::MALE);
        $this->addReference('campaign-finished', $campaignFinished);

        $campaignNoAdherent = $this->createCampaign(
            self::CAMPAIGN_4_UUID,
            'Campagne sans adhérents dispo à appeler',
            null,
            $team1,
            $nationalSurvey1,
            100,
            '+5 days'
        );
        $campaignNoAdherent->getAudience()->setGender(Genders::OTHER);
        $this->addReference('campaign-no-adherent', $campaignNoAdherent);

        $campaignWithAllAudienceParameters = $this->createCampaign(
            self::CAMPAIGN_5_UUID,
            'Campagne avec l\'audience contenant tous les paramètres',
            '**Campagne** avec l\'audience contenant tous les paramètres',
            $team2,
            $nationalSurvey2,
            10,
            '+5 days'
        );
        $audience = $campaignWithAllAudienceParameters->getAudience();
        $audience->setGender(Genders::MALE);
        $audience->setFirstName('Benjamin');
        $audience->setLastName('Duroc');
        $audience->setAgeMin(30);
        $audience->setAgeMax(45);
        $audience->setHasEmailSubscription(true);
        $audience->setHasSmsSubscription(false);
        $audience->setIsCertified(true);
        $audience->setIsCommitteeMember(true);
        $audience->setRegisteredSince(new \DateTime('2017-01-01'));
        $audience->setRegisteredUntil(new \DateTime('2018-01-01'));
        $zone = LoadGeoZoneData::getZoneReference($manager, 'zone_city_13055');
        $audience->setZone($zone);
        $audience->setZones([$zone]);
        $this->addReference('campaign-all-params', $campaignWithAllAudienceParameters);

        $manager->persist($campaign1);
        $manager->persist($campaign2);
        $manager->persist($campaignFinished);
        $manager->persist($campaignNoAdherent);
        $manager->persist($campaignWithAllAudienceParameters);

        $manager->persist($campaign = new Campaign(
            Uuid::fromString(self::CAMPAIGN_6_UUID),
            'Campagne permanente',
            <<<BRIEF
# Campagne permanente !
**Campagne** pour passer des appels à ses contacts
BRIEF,
            null, // Team
            null, // Audience
            $nationalSurvey2,
            42
        ));

        $campaign->setPermanent(true);

        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            LoadTeamData::class,
            LoadAdminData::class,
            LoadJecouteSurveyData::class,
        ];
    }

    private function createCampaign(
        string $uuid,
        string $title,
        ?string $brief,
        Team $team,
        Survey $survey,
        int $goal,
        string $finishAt
    ): Campaign {
        return new Campaign(Uuid::fromString($uuid), $title, $brief, $team, new AudienceSnapshot(), $survey, $goal, new Chronos($finishAt));
    }
}
