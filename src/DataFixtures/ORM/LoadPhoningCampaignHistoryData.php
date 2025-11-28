<?php

declare(strict_types=1);

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
use Doctrine\Persistence\ObjectManager;
use Ramsey\Uuid\Uuid;

class LoadPhoningCampaignHistoryData extends Fixture implements DependentFixtureInterface
{
    public const HISTORY_1_UUID = '47bf09fb-db03-40c3-b951-6fe6bbe1f055';
    public const HISTORY_2_UUID = 'a80248ff-384a-4f80-972a-177c3d0a77c4';
    public const HISTORY_3_UUID = 'b3c51626-164f-4fbd-9109-e70b20ab5788';
    public const HISTORY_4_UUID = '5587ce1f-bf4d-486f-a356-e75b06a62e2e';
    public const HISTORY_5_UUID = 'e369f31b-d339-4ba7-b303-baa980c430cc';

    public function load(ObjectManager $manager): void
    {
        /** @var NationalSurvey $nationalSurvey1 */
        $nationalSurvey1 = $this->getReference('national-survey-1', NationalSurvey::class);

        /** @var NationalSurvey $nationalSurvey3 */
        $nationalSurvey3 = $this->getReference('national-survey-3', NationalSurvey::class);

        /** @var Campaign $campaign1 */
        $campaign1 = $this->getReference('campaign-1', Campaign::class);
        /** @var Campaign $campaign2 */
        $campaign2 = $this->getReference('campaign-2', Campaign::class);
        /** @var Campaign $campaign3 */
        $campaign3 = $this->getReference('campaign-3', Campaign::class);

        $adherent3 = $this->getReference('adherent-3', Adherent::class);
        $adherent4 = $this->getReference('adherent-4', Adherent::class);
        $adherent12 = $this->getReference('adherent-12', Adherent::class);
        $deputy_75_1 = $this->getReference('deputy-75-1', Adherent::class);

        $phoningDataSurvey1 = $this->createPhoningCampaignHistory(
            $adherent3,
            $this->getReference('adherent-21', Adherent::class),
            $campaign1,
            CampaignHistoryStatusEnum::TO_UNSUBSCRIBE,
            $nationalSurvey1,
            new \DateTime('-1 minutes')
        );
        $phoningDataSurvey2 = $this->createPhoningCampaignHistory(
            $adherent3,
            $this->getReference('adherent-23', Adherent::class),
            $campaign2,
            CampaignHistoryStatusEnum::TO_UNSUBSCRIBE,
            $nationalSurvey1,
            new \DateTime('-2 minutes')
        );
        $phoningDataSurvey3 = $this->createPhoningCampaignHistory(
            $adherent3,
            $this->getReference('adherent-25', Adherent::class),
            $campaign1,
            CampaignHistoryStatusEnum::TO_UNJOIN,
            $nationalSurvey1,
            new \DateTime('-3 minutes')
        );
        $phoningDataSurvey4 = $this->createPhoningCampaignHistory(
            $deputy_75_1,
            $this->getReference('adherent-27', Adherent::class),
            $campaign2,
            CampaignHistoryStatusEnum::TO_UNJOIN,
            $nationalSurvey1,
            new \DateTime('-4 minutes')
        );
        $phoningDataSurvey5 = $this->createPhoningCampaignHistory(
            $adherent3,
            $this->getReference('adherent-29', Adherent::class),
            $campaign1,
            CampaignHistoryStatusEnum::FAILED,
            null,
            new \DateTime('2021-07-14')
        );
        $phoningDataSurvey6 = $this->createPhoningCampaignHistory(
            $adherent3,
            $this->getReference('adherent-33', Adherent::class),
            $campaign2,
            CampaignHistoryStatusEnum::FAILED,
            null,
            new \DateTime('-6 minutes')
        );
        $phoningDataSurvey7 = $this->createPhoningCampaignHistory(
            $adherent12,
            $this->getReference('adherent-35', Adherent::class),
            $campaign1,
            CampaignHistoryStatusEnum::INTERRUPTED_DONT_REMIND,
            $nationalSurvey1,
            new \DateTime('-7 minutes')
        );
        // should be returned for campaign1
        $phoningDataSurvey8 = $this->createPhoningCampaignHistory(
            $adherent3,
            $this->getReference('adherent-37', Adherent::class),
            $campaign2,
            CampaignHistoryStatusEnum::INTERRUPTED_DONT_REMIND,
            $nationalSurvey1,
            new \DateTime('-8 minutes')
        );
        $phoningDataSurvey9 = $this->createPhoningCampaignHistory(
            $adherent3,
            $this->getReference('adherent-39', Adherent::class),
            $campaign1,
            CampaignHistoryStatusEnum::SEND,
            null,
            new \DateTime(),
            self::HISTORY_1_UUID
        );
        $phoningDataSurvey9a = $this->createPhoningCampaignHistory(
            $adherent12,
            $this->getReference('adherent-39', Adherent::class),
            $campaign1,
            CampaignHistoryStatusEnum::SEND,
            null,
            new \DateTime('-5 days')
        );
        $phoningDataSurvey10 = $this->createPhoningCampaignHistory(
            $adherent12,
            $this->getReference('adherent-41', Adherent::class),
            $campaign1,
            CampaignHistoryStatusEnum::NOT_RESPOND,
            null,
            new \DateTime('-9 minutes')
        );
        $phoningDataSurvey10a = $this->createPhoningCampaignHistory(
            $adherent12,
            $this->getReference('adherent-41', Adherent::class),
            $campaign1,
            CampaignHistoryStatusEnum::NOT_RESPOND,
            null,
            new \DateTime('-10 days')
        );
        $phoningDataSurvey11 = $this->createPhoningCampaignHistory(
            $adherent12,
            $this->getReference('adherent-43', Adherent::class),
            $campaign1,
            CampaignHistoryStatusEnum::TO_REMIND,
            $nationalSurvey1
        );
        $phoningDataSurvey11a = $this->createPhoningCampaignHistory(
            $adherent12,
            $this->getReference('adherent-43', Adherent::class),
            $campaign1,
            CampaignHistoryStatusEnum::SEND,
            null,
            new \DateTime('-7 days')
        );
        $phoningDataSurvey12 = $this->createPhoningCampaignHistory(
            $adherent3,
            $this->getReference('adherent-45', Adherent::class),
            $campaign1,
            CampaignHistoryStatusEnum::INTERRUPTED,
            $nationalSurvey1,
            new \DateTime('-10 minutes')
        );
        $phoningDataSurvey13 = $this->createPhoningCampaignHistory(
            $adherent3,
            $this->getReference('adherent-47', Adherent::class),
            $campaign1,
            CampaignHistoryStatusEnum::COMPLETED,
            $nationalSurvey1,
            new \DateTime('-11 minutes')
        );
        $phoningDataSurvey14 = $this->createPhoningCampaignHistory(
            $adherent12,
            $this->getReference('adherent-49', Adherent::class),
            $campaign2,
            CampaignHistoryStatusEnum::COMPLETED,
            $nationalSurvey1,
            new \DateTime('-12 minutes')
        );
        // should be returned for campaign1
        $phoningDataSurvey15 = $this->createPhoningCampaignHistory(
            $adherent3,
            $this->getReference('adherent-2', Adherent::class),
            $campaign2,
            CampaignHistoryStatusEnum::COMPLETED,
            $nationalSurvey1,
            new \DateTime('-2 days')
        );

        $phoningDataSurvey16 = $this->createPhoningCampaignHistory(
            $adherent4,
            $this->getReference('adherent-40', Adherent::class),
            $campaign3,
            CampaignHistoryStatusEnum::COMPLETED,
            $nationalSurvey3,
            new \DateTime('-30 minutes'),
            self::HISTORY_3_UUID
        );
        $phoningDataSurvey16->setFinishAt(new \DateTime('-25 minutes'));
        $this->addReference('phoning-data-survey-1', $phoningDataSurvey16->getDataSurvey());

        $phoningDataSurvey17 = $this->createPhoningCampaignHistory(
            $adherent4,
            $this->getReference('adherent-34', Adherent::class),
            $campaign3,
            CampaignHistoryStatusEnum::COMPLETED,
            $nationalSurvey3,
            new \DateTime('-20 minutes'),
            self::HISTORY_4_UUID
        );
        $phoningDataSurvey17->setFinishAt(new \DateTime('-10 minutes'));
        $this->addReference('phoning-data-survey-2', $phoningDataSurvey17->getDataSurvey());

        $phoningDataSurvey18 = $this->createPhoningCampaignHistory(
            $adherent4,
            $this->getReference('adherent-35', Adherent::class),
            $campaign3,
            CampaignHistoryStatusEnum::FAILED,
            null,
            new \DateTime('-5 minutes')
        );

        $phoningDataSurvey19 = $this->createPhoningCampaignHistory(
            $adherent4,
            $this->getReference('adherent-37', Adherent::class),
            $campaign3,
            CampaignHistoryStatusEnum::COMPLETED,
            $nationalSurvey3,
            new \DateTime('-25 minutes'),
            self::HISTORY_5_UUID
        );
        $phoningDataSurvey19->setFinishAt(new \DateTime('-20 minutes'));
        $this->addReference('phoning-data-survey-3', $phoningDataSurvey19->getDataSurvey());

        $manager->persist($this->createPhoningCampaignHistory(
            $this->getReference('adherent-1', Adherent::class),
            null,
            $this->getReference('campaign-permanent', Campaign::class),
            CampaignHistoryStatusEnum::SEND,
            null,
            new \DateTime('-2 days'),
            self::HISTORY_2_UUID
        ));

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
        $manager->persist($phoningDataSurvey16);
        $manager->persist($phoningDataSurvey17);
        $manager->persist($phoningDataSurvey18);
        $manager->persist($phoningDataSurvey19);

        $manager->flush();
    }

    public function createPhoningCampaignHistory(
        Adherent $author,
        ?Adherent $called,
        Campaign $campaign,
        string $status,
        ?Survey $survey = null,
        ?\DateTime $beganAt = null,
        ?string $uuid = null,
    ): CampaignHistory {
        $campaignHistory = CampaignHistory::createForCampaign($campaign, $author, $called, $uuid ? Uuid::fromString($uuid) : Uuid::uuid4());

        $campaignHistory->setStatus($status);
        $campaignHistory->setBeginAt($beganAt ?? new \DateTime('-10 minutes'));

        if ($survey) {
            $dataSurvey = new DataSurvey($survey);
            $dataSurvey->setAuthor($author);
            $dataSurvey->setAuthorPostalCode($author->getPostalCode());

            $campaignHistory->setDataSurvey($dataSurvey);
        }

        return $campaignHistory;
    }

    public function getDependencies(): array
    {
        return [
            LoadJecouteSurveyData::class,
            LoadAdherentData::class,
            LoadPhoningCampaignData::class,
        ];
    }
}
