<?php

namespace App\DataFixtures\ORM;

use App\Entity\Adherent;
use App\Entity\Jecoute\CampaignHistory;
use App\Entity\Jecoute\DataSurvey;
use App\Entity\Jecoute\NationalSurvey;
use App\Entity\Jecoute\Survey;
use App\Entity\Phoning\Campaign;
use App\Phoning\DataSurveyStatusEnum;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class LoadPhoningCampaignHistoryData extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        /** @var NationalSurvey $nationalSurvey1 */
        $nationalSurvey1 = $this->getReference('national-survey-1');
        /** @var Campaign $campaign1 */
        $campaign1 = $this->getReference('campaign-1');
        /** @var Campaign $campaign2 */
        $campaign2 = $this->getReference('campaign-2');

        $adherent3 = $this->getReference('adherent-3');

        $phoningDataSurvey1 = $this->createPhoningCampaignHistory(
            $nationalSurvey1,
            $adherent3,
            $this->getReference('adherent-21'),
            $campaign1,
            DataSurveyStatusEnum::TO_UNSUBSCRIBE
        );
        $phoningDataSurvey2 = $this->createPhoningCampaignHistory(
            $nationalSurvey1,
            $adherent3,
            $this->getReference('adherent-23'),
            $campaign2,
            DataSurveyStatusEnum::TO_UNSUBSCRIBE
        );
        $phoningDataSurvey3 = $this->createPhoningCampaignHistory(
            $nationalSurvey1,
            $adherent3,
            $this->getReference('adherent-25'),
            $campaign1,
            DataSurveyStatusEnum::TO_UNJOIN
        );
        $phoningDataSurvey4 = $this->createPhoningCampaignHistory(
            $nationalSurvey1,
            $adherent3,
            $this->getReference('adherent-27'),
            $campaign2,
            DataSurveyStatusEnum::TO_UNJOIN
        );
        $phoningDataSurvey5 = $this->createPhoningCampaignHistory(
            $nationalSurvey1,
            $adherent3,
            $this->getReference('adherent-29'),
            $campaign1,
            DataSurveyStatusEnum::FAILED
        );
        $phoningDataSurvey6 = $this->createPhoningCampaignHistory(
            $nationalSurvey1,
            $adherent3,
            $this->getReference('adherent-33'),
            $campaign2,
            DataSurveyStatusEnum::FAILED
        );
        $phoningDataSurvey7 = $this->createPhoningCampaignHistory(
            $nationalSurvey1,
            $adherent3,
            $this->getReference('adherent-35'),
            $campaign1,
            DataSurveyStatusEnum::INTERRUPTED_DONT_REMIND
        );
        // should be returned for campaign1
        $phoningDataSurvey8 = $this->createPhoningCampaignHistory(
            $nationalSurvey1,
            $adherent3,
            $this->getReference('adherent-37'),
            $campaign2,
            DataSurveyStatusEnum::INTERRUPTED_DONT_REMIND
        );
        $phoningDataSurvey9 = $this->createPhoningCampaignHistory(
            $nationalSurvey1,
            $adherent3,
            $this->getReference('adherent-39'),
            $campaign1,
            DataSurveyStatusEnum::SEND
        );
        $phoningDataSurvey9a = $this->createPhoningCampaignHistory(
            $nationalSurvey1,
            $adherent3,
            $this->getReference('adherent-39'),
            $campaign1,
            DataSurveyStatusEnum::SEND,
            new \DateTime('-5 days')
        );
        $phoningDataSurvey10 = $this->createPhoningCampaignHistory(
            $nationalSurvey1,
            $adherent3,
            $this->getReference('adherent-41'),
            $campaign1,
            DataSurveyStatusEnum::NOT_RESPOND
        );
        $phoningDataSurvey10a = $this->createPhoningCampaignHistory(
            $nationalSurvey1,
            $adherent3,
            $this->getReference('adherent-41'),
            $campaign1,
            DataSurveyStatusEnum::NOT_RESPOND,
            new \DateTime('-10 days')
        );
        $phoningDataSurvey11 = $this->createPhoningCampaignHistory(
            $nationalSurvey1,
            $adherent3,
            $this->getReference('adherent-43'),
            $campaign1,
            DataSurveyStatusEnum::TO_REMIND
        );
        $phoningDataSurvey11a = $this->createPhoningCampaignHistory(
            $nationalSurvey1,
            $adherent3,
            $this->getReference('adherent-43'),
            $campaign1,
            DataSurveyStatusEnum::SEND,
            new \DateTime('-7 days')
        );
        $phoningDataSurvey12 = $this->createPhoningCampaignHistory(
            $nationalSurvey1,
            $adherent3,
            $this->getReference('adherent-45'),
            $campaign1,
            DataSurveyStatusEnum::INTERRUPTED
        );
        $phoningDataSurvey13 = $this->createPhoningCampaignHistory(
            $nationalSurvey1,
            $adherent3,
            $this->getReference('adherent-47'),
            $campaign1,
            DataSurveyStatusEnum::COMPLETED
        );
        $phoningDataSurvey14 = $this->createPhoningCampaignHistory(
            $nationalSurvey1,
            $adherent3,
            $this->getReference('adherent-49'),
            $campaign2,
            DataSurveyStatusEnum::COMPLETED
        );
        // should be returned for campaign1
        $phoningDataSurvey15 = $this->createPhoningCampaignHistory(
            $nationalSurvey1,
            $adherent3,
            $this->getReference('adherent-2'),
            $campaign2,
            DataSurveyStatusEnum::COMPLETED,
            new \DateTime('-2 days'),
            true
        );

        $manager->persist($phoningDataSurvey1);
        $manager->persist($phoningDataSurvey2);
        $manager->persist($phoningDataSurvey3);
        $manager->persist($phoningDataSurvey4);
        $manager->persist($phoningDataSurvey5);
        $manager->persist($phoningDataSurvey6);
        $manager->persist($phoningDataSurvey7);
        $manager->persist($phoningDataSurvey8);
        $manager->persist($phoningDataSurvey9);
        $manager->persist($phoningDataSurvey9a);
        $manager->persist($phoningDataSurvey10);
        $manager->persist($phoningDataSurvey10a);
        $manager->persist($phoningDataSurvey11);
        $manager->persist($phoningDataSurvey11a);
        $manager->persist($phoningDataSurvey12);
        $manager->persist($phoningDataSurvey13);
        $manager->persist($phoningDataSurvey14);
        $manager->persist($phoningDataSurvey15);

        $manager->flush();
    }

    public function createPhoningCampaignHistory(
        Survey $survey,
        Adherent $author,
        Adherent $called,
        Campaign $campaign,
        string $status,
        \DateTime $begitAt = null,
        bool $callMore = null
    ): CampaignHistory {
        $dataSurvey = new DataSurvey($survey);
        $dataSurvey->setAuthor($author);
        $phoningDataSurvey = new CampaignHistory($dataSurvey, $called);
        $phoningDataSurvey->setCampaign($campaign);
        $phoningDataSurvey->setStatus($status);
        $phoningDataSurvey->setBeginAt($begitAt ?? new \DateTime('-10 minutes'));
        if ($callMore) {
            $phoningDataSurvey->setCallMore($callMore);
        }

        return $phoningDataSurvey;
    }

    public function getDependencies()
    {
        return [
            LoadJecouteSurveyData::class,
            LoadAdherentData::class,
            LoadPhoningCampaignData::class,
        ];
    }
}
