<?php

namespace App\DataFixtures\ORM;

use App\Entity\Adherent;
use App\Entity\Jecoute\DataSurvey;
use App\Entity\Jecoute\NationalSurvey;
use App\Entity\Jecoute\Survey;
use App\Entity\Phoning\Campaign;
use App\Entity\Phoning\CampaignHistory;
use App\Phoning\CampaignHistoryStatusEnum;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Ramsey\Uuid\Uuid;

class LoadPhoningCampaignHistoryData extends Fixture implements DependentFixtureInterface
{
    public const HISTORY_1_UUID = '47bf09fb-db03-40c3-b951-6fe6bbe1f055';

    public function load(ObjectManager $manager)
    {
        /** @var NationalSurvey $nationalSurvey1 */
        $nationalSurvey1 = $this->getReference('national-survey-1');
        /** @var Campaign $campaign1 */
        $campaign1 = $this->getReference('campaign-1');
        /** @var Campaign $campaign2 */
        $campaign2 = $this->getReference('campaign-2');

        $adherent3 = $this->getReference('adherent-3');
        $adherent12 = $this->getReference('adherent-12');
        $deputy_75_1 = $this->getReference('deputy-75-1');

        $phoningDataSurvey1 = $this->createPhoningCampaignHistory(
            $adherent3,
            $this->getReference('adherent-21'),
            $campaign1,
            CampaignHistoryStatusEnum::TO_UNSUBSCRIBE,
            $nationalSurvey1
        );
        $phoningDataSurvey2 = $this->createPhoningCampaignHistory(
            $adherent3,
            $this->getReference('adherent-23'),
            $campaign2,
            CampaignHistoryStatusEnum::TO_UNSUBSCRIBE,
            $nationalSurvey1
        );
        $phoningDataSurvey3 = $this->createPhoningCampaignHistory(
            $adherent3,
            $this->getReference('adherent-25'),
            $campaign1,
            CampaignHistoryStatusEnum::TO_UNJOIN,
            $nationalSurvey1
        );
        $phoningDataSurvey4 = $this->createPhoningCampaignHistory(
            $deputy_75_1,
            $this->getReference('adherent-27'),
            $campaign2,
            CampaignHistoryStatusEnum::TO_UNJOIN,
            $nationalSurvey1
        );
        $phoningDataSurvey5 = $this->createPhoningCampaignHistory(
            $adherent3,
            $this->getReference('adherent-29'),
            $campaign1,
            CampaignHistoryStatusEnum::FAILED
        );
        $phoningDataSurvey6 = $this->createPhoningCampaignHistory(
            $adherent3,
            $this->getReference('adherent-33'),
            $campaign2,
            CampaignHistoryStatusEnum::FAILED
        );
        $phoningDataSurvey7 = $this->createPhoningCampaignHistory(
            $adherent12,
            $this->getReference('adherent-35'),
            $campaign1,
            CampaignHistoryStatusEnum::INTERRUPTED_DONT_REMIND,
            $nationalSurvey1
        );
        // should be returned for campaign1
        $phoningDataSurvey8 = $this->createPhoningCampaignHistory(
            $adherent3,
            $this->getReference('adherent-37'),
            $campaign2,
            CampaignHistoryStatusEnum::INTERRUPTED_DONT_REMIND,
            $nationalSurvey1
        );
        $phoningDataSurvey9 = $this->createPhoningCampaignHistory(
            $adherent3,
            $this->getReference('adherent-39'),
            $campaign1,
            CampaignHistoryStatusEnum::SEND,
            null,
            new \DateTime(),
            true,
            self::HISTORY_1_UUID
        );
        $phoningDataSurvey9a = $this->createPhoningCampaignHistory(
            $adherent12,
            $this->getReference('adherent-39'),
            $campaign1,
            CampaignHistoryStatusEnum::SEND,
            null,
            new \DateTime('-5 days')
        );
        $phoningDataSurvey10 = $this->createPhoningCampaignHistory(
            $adherent12,
            $this->getReference('adherent-41'),
            $campaign1,
            CampaignHistoryStatusEnum::NOT_RESPOND
        );
        $phoningDataSurvey10a = $this->createPhoningCampaignHistory(
            $adherent12,
            $this->getReference('adherent-41'),
            $campaign1,
            CampaignHistoryStatusEnum::NOT_RESPOND,
            null,
            new \DateTime('-10 days')
        );
        $phoningDataSurvey11 = $this->createPhoningCampaignHistory(
            $adherent12,
            $this->getReference('adherent-43'),
            $campaign1,
            CampaignHistoryStatusEnum::TO_REMIND,
            $nationalSurvey1
        );
        $phoningDataSurvey11a = $this->createPhoningCampaignHistory(
            $adherent12,
            $this->getReference('adherent-43'),
            $campaign1,
            CampaignHistoryStatusEnum::SEND,
            null,
            new \DateTime('-7 days')
        );
        $phoningDataSurvey12 = $this->createPhoningCampaignHistory(
            $adherent3,
            $this->getReference('adherent-45'),
            $campaign1,
            CampaignHistoryStatusEnum::INTERRUPTED,
            $nationalSurvey1,
        );
        $phoningDataSurvey13 = $this->createPhoningCampaignHistory(
            $adherent3,
            $this->getReference('adherent-47'),
            $campaign1,
            CampaignHistoryStatusEnum::COMPLETED,
            $nationalSurvey1,
        );
        $phoningDataSurvey14 = $this->createPhoningCampaignHistory(
            $adherent12,
            $this->getReference('adherent-49'),
            $campaign2,
            CampaignHistoryStatusEnum::COMPLETED,
            $nationalSurvey1
        );
        // should be returned for campaign1
        $phoningDataSurvey15 = $this->createPhoningCampaignHistory(
            $adherent3,
            $this->getReference('adherent-2'),
            $campaign2,
            CampaignHistoryStatusEnum::COMPLETED,
            $nationalSurvey1,
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
        Adherent $author,
        Adherent $called,
        Campaign $campaign,
        string $status,
        Survey $survey = null,
        \DateTime $begitAt = null,
        bool $callMore = null,
        string $uuid = null
    ): CampaignHistory {
        $campaignHistory = CampaignHistory::createForCampaign($campaign, $author, $called, $uuid ? Uuid::fromString($uuid) : Uuid::uuid4());

        $campaignHistory->setStatus($status);
        $campaignHistory->setBeginAt($begitAt ?? new \DateTime('-10 minutes'));
        $campaignHistory->setCallMore($callMore);

        if ($survey) {
            $dataSurvey = new DataSurvey($survey);
            $dataSurvey->setAuthor($author);

            $campaignHistory->setDataSurvey($dataSurvey);
        }

        return $campaignHistory;
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
