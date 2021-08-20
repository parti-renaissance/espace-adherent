<?php

namespace App\DataFixtures\ORM;

use App\Entity\Audience\AudienceBackup;
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

    public function load(ObjectManager $manager)
    {
        $campaign1 = $this->createCampaign(
            self::CAMPAIGN_1_UUID,
            'Campagne pour les hommes',
            $this->getReference('team-1'),
            500,
            '+10 days'
        );
        $campaign1->getAudience()->setGender(Genders::MALE);

        $campaign2 = $this->createCampaign(
            self::CAMPAIGN_2_UUID,
            'Campagne pour les femmes',
            $this->getReference('team-2'),
            500,
            '+15 days'
        );
        $campaign2->getAudience()->setGender(Genders::FEMALE);

        $manager->persist($campaign1);
        $manager->persist($campaign2);

        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            LoadTeamData::class,
            LoadAdminData::class,
        ];
    }

    private function createCampaign(string $uuid, string $title, Team $team, int $goal, string $finishAt): Campaign
    {
        return new Campaign(Uuid::fromString($uuid), $title, $team, new AudienceBackup(), $goal, new Chronos($finishAt));
    }
}
