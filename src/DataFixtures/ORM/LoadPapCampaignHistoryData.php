<?php

declare(strict_types=1);

namespace App\DataFixtures\ORM;

use App\Entity\Adherent;
use App\Entity\Jecoute\DataSurvey;
use App\Entity\Jecoute\NationalSurvey;
use App\Entity\Jecoute\Survey;
use App\Entity\Pap\Building;
use App\Entity\Pap\Campaign;
use App\Entity\Pap\CampaignHistory;
use App\Jecoute\AgeRangeEnum;
use App\Jecoute\GenderEnum;
use App\Jecoute\ProfessionEnum;
use App\Pap\CampaignHistoryStatusEnum;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Ramsey\Uuid\Uuid;

class LoadPapCampaignHistoryData extends Fixture implements DependentFixtureInterface
{
    public const HISTORY_1_UUID = '6b3d2e20-8f66-4cbb-a7ce-2a1b740c75da';

    public function load(ObjectManager $manager): void
    {
        /** @var Campaign $campaign1 */
        $campaign1 = $this->getReference('pap-campaign-1', Campaign::class);
        /** @var Campaign $campaign2 */
        $campaign2 = $this->getReference('pap-campaign-2', Campaign::class);
        /** @var Campaign $campaign75_08_r */
        $campaign75_08_r = $this->getReference('pap-campaign-75-08-r', Campaign::class);
        /** @var Campaign $campaignFinished */
        $campaignFinished = $this->getReference('pap-campaign-finished', Campaign::class);
        /** @var Campaign $campaign92 */
        $campaign92 = $this->getReference('pap-campaign-92', Campaign::class);

        $adherent3 = $this->getReference('adherent-3', Adherent::class);
        $adherent12 = $this->getReference('adherent-12', Adherent::class);
        $adherent16 = $this->getReference('adherent-16', Adherent::class);

        $nationalSurvey3 = $this->getReference('national-survey-3', NationalSurvey::class);

        /** @var Building $building3 */
        $building3 = $this->getReference('building-3', Building::class);
        /** @var Building $building92_1 */
        $building92_1 = $this->getReference('building-92-1', Building::class);
        /** @var Building $building_75_08_1 */
        $building_75_08_1 = $this->getReference('building-75-08-1', Building::class);

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

        $manager->persist($papCampaignHistory = $this->createPapCampaignHistory(
            $campaign1,
            $building3,
            CampaignHistoryStatusEnum::ACCEPT_TO_ANSWER,
            'A',
            1,
            '11',
            $adherent16,
            $nationalSurvey3,
            new \DateTime('-35 minutes'),
            new \DateTime('-30 minutes')
        ));
        $papCampaignHistory->setFirstName('Javier');
        $papCampaignHistory->setLastName('Latombe');
        $papCampaignHistory->setProfession(ProfessionEnum::FARMERS);
        $papCampaignHistory->setAgeRange(AgeRangeEnum::BETWEEN_25_39);
        $papCampaignHistory->setVoterPostalCode('94081');
        $papCampaignHistory->setGender(GenderEnum::OTHER);
        $this->addReference('pap-data-survey-1', $papCampaignHistory->getDataSurvey());

        $manager->persist($papCampaignHistory = $this->createPapCampaignHistory(
            $campaign1,
            $building3,
            CampaignHistoryStatusEnum::ACCEPT_TO_ANSWER,
            'A',
            1,
            '12',
            $adherent16,
            $nationalSurvey3,
            new \DateTime('-25 minutes'),
            new \DateTime('-18 minutes')
        ));
        $this->addReference('pap-data-survey-2', $papCampaignHistory->getDataSurvey());

        $manager->persist($papCampaignHistory = $this->createPapCampaignHistory(
            $campaign1,
            $building3,
            CampaignHistoryStatusEnum::ACCEPT_TO_ANSWER,
            'A',
            1,
            '13',
            $adherent16,
            $nationalSurvey3,
            new \DateTime('-5 minutes')
        ));
        $this->addReference('pap-data-survey-3', $papCampaignHistory->getDataSurvey());
        $statsFloor0 = $building3->getBuildingBlockByName('A')->getFloorByNumber(0)->findStatisticsForCampaign($campaign1);
        $statsFloor1 = $building3->getBuildingBlockByName('A')->getFloorByNumber(1)->findStatisticsForCampaign($campaign1);
        $statsFloor0->setVisitedDoors(['01', '02']);
        $statsFloor1->setVisitedDoors(['11', '12', '13']);

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

        $manager->persist($this->createPapCampaignHistory(
            $campaignFinished,
            $building3,
            CampaignHistoryStatusEnum::CONTACT_LATER,
            'A',
            0,
            '01',
            $adherent12,
            null,
            new \DateTime('2021-11-10 10:10:10'),
            new \DateTime('2021-11-10 10:12:30')
        ));
        $stats = $building3->getBuildingBlockByName('A')->getFloorByNumber(0)->findStatisticsForCampaign($campaignFinished);
        $stats->setVisitedDoors(['01']);

        $manager->persist($this->createPapCampaignHistory(
            $campaign92,
            $building92_1,
            CampaignHistoryStatusEnum::DOOR_CLOSED,
            'A',
            0,
            '01',
            $adherent12,
            null,
            new \DateTime('-2 hours'),
            new \DateTime('-2 hours')
        ));
        $stats = $building92_1->getBuildingBlockByName('A')->getFloorByNumber(0)->findStatisticsForCampaign($campaign92);
        $stats->setVisitedDoors(['01']);

        $manager->persist($this->createPapCampaignHistory(
            $campaign75_08_r,
            $building_75_08_1,
            CampaignHistoryStatusEnum::DOOR_CLOSED,
            'A',
            0,
            '01',
            $adherent12,
            null,
            new \DateTime('-1 hour'),
            new \DateTime('-1 hour')
        ));
        $stats = $building_75_08_1->getBuildingBlockByName('A')->getFloorByNumber(0)->findStatisticsForCampaign($campaign75_08_r);
        $stats->setVisitedDoors(['01']);

        $manager->flush();
    }

    public function createPapCampaignHistory(
        Campaign $campaign,
        Building $building,
        string $status,
        string $buildingBlock,
        int $floor,
        string $door,
        ?Adherent $questioner = null,
        ?Survey $survey = null,
        ?\DateTime $beginAt = null,
        ?\DateTime $finishAt = null,
        ?string $uuid = null,
    ): CampaignHistory {
        $campaignHistory = new CampaignHistory($uuid ? Uuid::fromString($uuid) : Uuid::uuid4());
        $campaignHistory->setCampaign($campaign);
        $campaignHistory->setBuilding($building);
        $campaignHistory->setStatus($status);
        $campaignHistory->setBuildingBlock($buildingBlock);
        $campaignHistory->setFloor($floor);
        $campaignHistory->setDoor($door);
        $campaignHistory->setCreatedAt($beginAt ?? new \DateTime('-10 minutes'));
        $campaignHistory->setBeginAt($beginAt ?? new \DateTime('-10 minutes'));
        $campaignHistory->setFinishAt($finishAt ?? new \DateTime('-5 minutes'));
        $campaignHistory->setQuestioner($questioner);

        if ($survey) {
            $dataSurvey = new DataSurvey($survey);
            if ($questioner) {
                $dataSurvey->setAuthor($questioner);
                $dataSurvey->setAuthorPostalCode($questioner->getPostalCode());
                $campaignHistory->setEmailAddress(\sprintf('%s-%s-%s@test-en-marche-dev.com', $building->getId(), $buildingBlock, $floor));
            }

            $campaignHistory->setDataSurvey($dataSurvey);
        }

        return $campaignHistory;
    }

    public function getDependencies(): array
    {
        return [
            LoadJecouteSurveyData::class,
            LoadAdherentData::class,
            LoadPapCampaignData::class,
            LoadPapBuildingData::class,
        ];
    }
}
