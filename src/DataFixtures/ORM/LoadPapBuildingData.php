<?php

namespace App\DataFixtures\ORM;

use App\Entity\Adherent;
use App\Entity\Pap\Building;
use App\Entity\Pap\BuildingBlock;
use App\Entity\Pap\BuildingBlockStatistics;
use App\Entity\Pap\BuildingEvent;
use App\Entity\Pap\BuildingStatistics;
use App\Entity\Pap\Campaign;
use App\Entity\Pap\CampaignStatisticsInterface;
use App\Entity\Pap\Floor;
use App\Entity\Pap\FloorStatistics;
use App\Pap\BuildingEventActionEnum;
use App\Pap\BuildingEventTypeEnum;
use App\Pap\BuildingStatusEnum;
use App\Pap\BuildingTypeEnum;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Ramsey\Uuid\Uuid;

class LoadPapBuildingData extends Fixture implements DependentFixtureInterface
{
    public const BUILDING_01_UUID = '2fbe7b02-944d-4abd-be3d-f9b2944917a9';
    public const BUILDING_02_UUID = 'faf30370-80c5-4a46-8c31-f6a361bfa23b';
    public const BUILDING_03_UUID = '2bffd913-34fe-48ad-95f4-7381812b93dd';
    public const BUILDING_04_UUID = '0b81ff3d-f895-4e3f-bf6d-ff2a659c1c6f';
    public const BUILDING_05_UUID = '22f94373-6186-4c6a-a3d5-fd0b8b3d92cf';

    public const BUILDING_BLOCK_01_UUID = '40c972e7-3ae9-45d7-8d18-4df636382a01';
    public const BUILDING_BLOCK_02_UUID = '55fc7719-d1a8-47c5-a08a-812e7ce1d6dc';
    public const BUILDING_BLOCK_03_UUID = 'd2a9605a-7f03-49f8-956b-3870cb77dad4';
    public const BUILDING_BLOCK_04_UUID = '734d965b-0b3a-4258-a32e-0fca71a451e7';
    public const BUILDING_BLOCK_05_UUID = '19e469ea-f56d-4f1d-a942-b4cc368aed8b';
    public const BUILDING_BLOCK_06_UUID = '817722ed-0396-444f-987a-d4d336242e41';

    public const FLOOR_01_UUID = 'bc407152-703a-4a08-ba70-27fcb87329c8';
    public const FLOOR_02_UUID = '7fb64baa-48be-4e55-8955-f9100f79143f';
    public const FLOOR_03_UUID = 'bb1d3001-a8fb-435a-afab-61feb637508c';
    public const FLOOR_04_UUID = '78390510-05b2-48a8-822d-a2b2fd5d003a';
    public const FLOOR_05_UUID = '7422bbe5-1522-42c4-8908-11658027f070';
    public const FLOOR_06_UUID = '2f974e1a-595a-4972-9e15-147d759a2f60';
    public const FLOOR_07_UUID = '2fda7bc4-d21d-482c-80a8-48c5d83fcc34';

    public function load(ObjectManager $manager)
    {
        $events = [];
        $campaign1 = $this->getReference('pap-campaign-1');
        $campaign92 = $this->getReference('pap-campaign-92');
        $building = new Building(Uuid::fromString(self::BUILDING_01_UUID));
        $building->setType(BuildingTypeEnum::BUILDING);
        $building->setAddress($this->getReference('address-1'));
        $building->setCurrentCampaign($campaign1);
        $building->addStatistic(new BuildingStatistics($building, $this->getReference('pap-campaign-2')));
        $building->addStatistic($stats = new BuildingStatistics($building, $campaign1, BuildingStatusEnum::ONGOING));
        $stats->setNbVisitedDoors(1);
        $stats->setLastPassage(new \DateTime('- 10 days'));
        $stats->setLastPassageDoneBy($this->getReference('adherent-33'));
        $this->createBuildingBlock(
            self::BUILDING_BLOCK_01_UUID,
            'A',
            $building,
            [$campaign1],
            $events,
            3,
            [self::FLOOR_01_UUID, self::FLOOR_02_UUID, self::FLOOR_03_UUID],
            BuildingStatusEnum::COMPLETED,
            $this->getReference('adherent-31'),
            new \DateTime('-10 days')
        );
        $this->addReference('building-1', $building);
        $manager->persist($building);

        $building = new Building(Uuid::fromString(self::BUILDING_02_UUID));
        $building->setType(BuildingTypeEnum::BUILDING);
        $building->setAddress($this->getReference('address-2'));
        $building->setCurrentCampaign($campaign1);
        $building->addStatistic(new BuildingStatistics($building, $campaign1));
        $this->createBuildingBlock(
            self::BUILDING_BLOCK_02_UUID,
            'A',
            $building,
            [$this->getReference('pap-campaign-1')],
            $events,
            2,
            [self::FLOOR_04_UUID, self::FLOOR_05_UUID],
            BuildingStatusEnum::COMPLETED,
            $this->getReference('adherent-32'),
            new \DateTime('-5 days')
        );
        $this->createBuildingBlock(
            self::BUILDING_BLOCK_03_UUID,
            'B',
            $building,
            [$this->getReference('pap-campaign-1')],
            $events,
            2,
            [self::FLOOR_06_UUID, self::FLOOR_07_UUID],
            BuildingStatusEnum::ONGOING,
            $this->getReference('adherent-32'),
            new \DateTime('-5 days')
        );
        $this->addReference('building-2', $building);
        $manager->persist($building);

        $building = new Building(Uuid::fromString(self::BUILDING_03_UUID));
        $building->setType(BuildingTypeEnum::BUILDING);
        $building->setAddress($this->getReference('address-3'));
        $building->setCurrentCampaign($campaign1);
        $building->addStatistic(new BuildingStatistics($building, $campaign1));
        $this->createBuildingBlock(
            self::BUILDING_BLOCK_04_UUID,
            'A',
            $building,
            [$this->getReference('pap-campaign-1'), $this->getReference('pap-campaign-finished')],
            $events,
            11,
            [],
            BuildingStatusEnum::ONGOING,
            $this->getReference('adherent-32'),
            new \DateTime('-3 days')
        );
        $this->addReference('building-3', $building);
        $manager->persist($building);

        $building = new Building(Uuid::fromString(self::BUILDING_04_UUID));
        $building->setType(BuildingTypeEnum::BUILDING);
        $building->setAddress($this->getReference('address-4'));
        $building->setCurrentCampaign($campaign1);
        $building->addStatistic(new BuildingStatistics($building, $campaign1));
        $this->createBuildingBlock(
            self::BUILDING_BLOCK_05_UUID,
            'A',
            $building,
            [$this->getReference('pap-campaign-2')],
            $events
        );
        $this->addReference('building-4', $building);
        $manager->persist($building);

        $building = new Building(Uuid::fromString(self::BUILDING_05_UUID));
        $building->setType(BuildingTypeEnum::BUILDING);
        $building->setAddress($this->getReference('address-92-1'));
        $building->setCurrentCampaign($campaign92);
        $building->addStatistic(new BuildingStatistics($building, $campaign92));
        $this->createBuildingBlock(
            self::BUILDING_BLOCK_06_UUID,
            'A',
            $building,
            [$this->getReference('pap-campaign-92')],
            $events
        );
        $this->addReference('building-92-1', $building);
        $manager->persist($building);

        foreach ($events as $event) {
            $manager->persist($event);
        }

        $manager->flush();
    }

    private function createBuildingBlock(
        string $uuid,
        string $name,
        Building $building,
        array $campaigns,
        array &$events,
        int $floors = 1,
        array $floorsUuids = [],
        string $status = BuildingStatusEnum::ONGOING,
        Adherent $createdBy = null,
        \DateTime $createdAt = null
    ): void {
        $createdAt = $createdAt ?? new \DateTime();

        $buildingBlock = new BuildingBlock($name, $building, Uuid::fromString($uuid));
        $buildingBlock->setCreatedByAdherent($createdBy ?? $this->getReference('adherent-20'));
        $buildingBlock->setCreatedAt($createdAt);
        $building->addBuildingBlock($buildingBlock);
        foreach ($campaigns as $campaign) {
            $buildingBlock->addStatistic($stats = new BuildingBlockStatistics($buildingBlock, $campaign, $status));
            if (BuildingStatusEnum::COMPLETED === $status) {
                $events[] = $this->createBuildingEvent(
                    BuildingEventTypeEnum::BUILDING_BLOCK,
                    BuildingEventActionEnum::CLOSE,
                    $buildingBlock->getName(),
                    $building,
                    $campaign,
                    $createdBy,
                    new \DateTime(),
                    $stats
                );
            }
        }

        for ($number = 0; $number < $floors; ++$number) {
            $floor = new Floor($number, $buildingBlock, isset($floorsUuids[$number]) ? Uuid::fromString($floorsUuids[$number]) : null);
            $floor->setCreatedByAdherent($createdBy ?? $this->getReference('adherent-20'));
            $floor->setCreatedAt($createdAt);
            $buildingBlock->addFloor($floor);
            foreach ($campaigns as $campaign) {
                $floor->addStatistic($stats = new FloorStatistics($floor, $campaign, $status));
                if (BuildingStatusEnum::COMPLETED === $status) {
                    $events[] = $this->createBuildingEvent(
                        BuildingEventTypeEnum::FLOOR,
                        BuildingEventActionEnum::CLOSE,
                        sprintf('%s-%s', $buildingBlock->getName(), $floor->getNumber()),
                        $building,
                        $campaign,
                        $createdBy,
                        new \DateTime(),
                        $stats
                    );
                }
            }
        }
    }

    private function createBuildingEvent(
        string $type,
        string $action,
        string $identifier,
        Building $building,
        Campaign $campaign,
        Adherent $createdBy,
        ?\DateTime $createdAt = null,
        ?CampaignStatisticsInterface $stats = null
    ): BuildingEvent {
        $event = new BuildingEvent(
            $building,
            $campaign,
            $action,
            $type,
            $identifier
        );
        $event->setAuthor($createdBy);
        if (BuildingEventActionEnum::CLOSE) {
            $stats->setClosedBy($createdBy);
            $stats->setClosedAt($createdAt ?? new \DateTime());
        }

        return $event;
    }

    public function getDependencies()
    {
        return [
            LoadAdherentData::class,
            LoadPapAddressData::class,
            LoadPapCampaignData::class,
        ];
    }
}
