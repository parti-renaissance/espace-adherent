<?php

namespace App\DataFixtures\ORM;

use App\Entity\Adherent;
use App\Entity\Jecoute\DataSurvey;
use App\Entity\Jecoute\Survey;
use App\Entity\Pap\Building;
use App\Entity\Pap\Campaign;
use App\Entity\Pap\CampaignHistory;
use App\Pap\CampaignHistoryStatusEnum;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Ramsey\Uuid\Uuid;

class LoadPapCampaignHistoryData extends Fixture implements DependentFixtureInterface
{
    public const HISTORY_1_UUID = '6b3d2e20-8f66-4cbb-a7ce-2a1b740c75da';

    public function load(ObjectManager $manager)
    {
        /** @var Campaign $campaign1 */
        $campaign1 = $this->getReference('pap-campaign-1');
        /** @var Campaign $campaign2 */
        $campaign2 = $this->getReference('pap-campaign-2');

        $adherent3 = $this->getReference('adherent-3');
        $adherent12 = $this->getReference('adherent-12');
        $adherent16 = $this->getReference('adherent-16');

        $nationalSurvey1 = $this->getReference('national-survey-1');

        /** @var Building $building3 */
        $building3 = $this->getReference('building-3');

        $manager->persist($this->createPapCampaignHistory(
            $campaign1,
            $building3,
            CampaignHistoryStatusEnum::DOOR_OPEN,
            'A',
            0,
            '01',
            $adherent3,
            null,
            new \DateTime('-2 days -15 minutes'),
            new \DateTime('-2 days'),
            self::HISTORY_1_UUID
        ));

        $manager->persist($this->createPapCampaignHistory(
            $campaign1,
            $building3,
            CampaignHistoryStatusEnum::DONT_ACCEPT_TO_ANSWER,
            'A',
            0,
            '02',
            $adherent3,
            null,
            new \DateTime('-2 days -10 minutes'),
            new \DateTime('-2 days -9 minutes')
        ));

        $manager->persist($this->createPapCampaignHistory(
            $campaign1,
            $building3,
            CampaignHistoryStatusEnum::ACCEPT_TO_ANSWER,
            'A',
            1,
            '11',
            $adherent16,
            $nationalSurvey1,
            new \DateTime('-5 minutes')
        ));
        $statsFloor0 = $building3->getBuildingBlockByName('A')->getFloorByNumber(0)->findStatisticsForCampaign($campaign1);
        $statsFloor1 = $building3->getBuildingBlockByName('A')->getFloorByNumber(1)->findStatisticsForCampaign($campaign1);
        $statsFloor0->setVisitedDoors(['01', '02']);
        $statsFloor1->setVisitedDoors(['11']);

        $manager->persist($this->createPapCampaignHistory(
            $campaign2,
            $building3,
            CampaignHistoryStatusEnum::CONTACT_LATER,
            'A',
            3,
            '33',
            $adherent12
        ));
        $stats = $building3->getBuildingBlockByName('A')->getFloorByNumber(3)->findStatisticsForCampaign($campaign1);
        $stats->setVisitedDoors(['33']);

        $manager->flush();
    }

    public function createPapCampaignHistory(
        Campaign $campaign,
        Building $building,
        string $status,
        string $buildingBlock,
        int $floor,
        string $door,
        Adherent $questioner = null,
        Survey $survey = null,
        \DateTime $createdAt = null,
        \DateTime $finishAt = null,
        string $uuid = null
    ): CampaignHistory {
        $campaignHistory = new CampaignHistory($uuid ? Uuid::fromString($uuid) : Uuid::uuid4());
        $campaignHistory->setCampaign($campaign);
        $campaignHistory->setBuilding($building);
        $campaignHistory->setStatus($status);
        $campaignHistory->setBuildingBlock($buildingBlock);
        $campaignHistory->setFloor($floor);
        $campaignHistory->setDoor($door);
        $campaignHistory->setCreatedAt($createdAt ?? new \DateTime('-10 minutes'));
        $campaignHistory->setFinishAt($finishAt ?? new \DateTime('-5 minutes'));
        $campaignHistory->setQuestioner($questioner);

        if ($survey) {
            $dataSurvey = new DataSurvey($survey);
            if ($questioner) {
                $dataSurvey->setAuthor($questioner);
                $dataSurvey->setAuthorPostalCode($questioner->getPostalCode());
                $campaignHistory->setEmailAddress(sprintf('%s-%s-%s@test-en-marche-dev.com', $building->getId(), $buildingBlock, $floor));
            }

            $campaignHistory->setDataSurvey($dataSurvey);
        }

        return $campaignHistory;
    }

    public function getDependencies()
    {
        return [
            LoadJecouteSurveyData::class,
            LoadAdherentData::class,
            LoadPapCampaignData::class,
            LoadPapBuildingData::class,
        ];
    }
}
