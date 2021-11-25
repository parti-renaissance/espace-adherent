<?php

namespace App\DataFixtures\ORM;

use App\Entity\Adherent;
use App\Entity\Pap\Building;
use App\Entity\Pap\BuildingBlock;
use App\Entity\Pap\BuildingBlockStatistics;
use App\Entity\Pap\BuildingStatistics;
use App\Entity\Pap\Campaign;
use App\Entity\Pap\Floor;
use App\Entity\Pap\FloorStatistics;
use App\Pap\BuildingStatusEnum;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Ramsey\Uuid\Uuid;

class LoadPapBuildingData extends Fixture implements DependentFixtureInterface
{
    private const BUILDING_01_UUID = '2fbe7b02-944d-4abd-be3d-f9b2944917a9';
    private const BUILDING_02_UUID = 'faf30370-80c5-4a46-8c31-f6a361bfa23b';
    private const BUILDING_03_UUID = '2bffd913-34fe-48ad-95f4-7381812b93dd';
    private const BUILDING_04_UUID = '0b81ff3d-f895-4e3f-bf6d-ff2a659c1c6f';

    public function load(ObjectManager $manager)
    {
        $campaign1 = $this->getReference('pap-campaign-1');
        $building = new Building(Uuid::fromString(self::BUILDING_01_UUID));
        $building->setAddress($this->getReference('address-1'));
        $building->setCurrentCampaign($campaign1);
        $building->addStatistic(new BuildingStatistics($building, $this->getReference('pap-campaign-2')));
        $building->addStatistic($stats = new BuildingStatistics($building, $campaign1));
        $stats->setLastPassage(new \DateTime('- 10 days'));
        $stats->setLastPassageDoneBy($this->getReference('adherent-33'));
        $this->createBuildingBlock(
            'Bâtiment A',
            $building,
            $campaign1,
            3,
            BuildingStatusEnum::COMPLETED,
            $this->getReference('adherent-31'),
            new \DateTime('-10 days')
        );
        $this->addReference('building-1', $building);
        $manager->persist($building);

        $building = new Building(Uuid::fromString(self::BUILDING_02_UUID));
        $building->setAddress($this->getReference('address-2'));
        $building->setCurrentCampaign($campaign1);
        $building->addStatistic(new BuildingStatistics($building, $campaign1));
        $this->createBuildingBlock(
            'Bâtiment A',
            $building,
            $this->getReference('pap-campaign-1'),
            2,
            BuildingStatusEnum::COMPLETED,
            $this->getReference('adherent-32'),
            new \DateTime('-5 days')
        );
        $this->createBuildingBlock(
            'Bâtiment B',
            $building,
            $this->getReference('pap-campaign-1'),
            2,
            BuildingStatusEnum::ONGOING,
            $this->getReference('adherent-32'),
            new \DateTime('-5 days')
        );
        $this->addReference('building-2', $building);
        $manager->persist($building);

        $building = new Building(Uuid::fromString(self::BUILDING_03_UUID));
        $building->setAddress($this->getReference('address-3'));
        $building->setCurrentCampaign($campaign1);
        $building->addStatistic(new BuildingStatistics($building, $campaign1));
        $this->createBuildingBlock(
            'Bâtiment A',
            $building,
            $this->getReference('pap-campaign-1'),
            11,
            BuildingStatusEnum::ONGOING,
            $this->getReference('adherent-32'),
            new \DateTime('-3 days')
        );
        $this->addReference('building-3', $building);
        $manager->persist($building);

        $building = new Building(Uuid::fromString(self::BUILDING_04_UUID));
        $building->setAddress($this->getReference('address-4'));
        $building->setCurrentCampaign($campaign1);
        $building->addStatistic(new BuildingStatistics($building, $campaign1));
        $this->createBuildingBlock('Bâtiment A', $building, $this->getReference('pap-campaign-2'));
        $this->addReference('building-4', $building);
        $manager->persist($building);

        $manager->flush();
    }

    private function createBuildingBlock(
        string $name,
        Building $building,
        Campaign $campaign,
        int $floors = 1,
        string $status = BuildingStatusEnum::ONGOING,
        Adherent $createdBy = null,
        \DateTime $createdAt = null
    ): void {
        $createdAt = $createdAt ?? new \DateTime();

        $buildingBlock = new BuildingBlock($name, $building);
        $buildingBlock->setCreatedByAdherent($createdBy ?? $this->getReference('adherent-20'));
        $buildingBlock->setCreatedAt($createdAt);
        $building->addBuildingBlock($buildingBlock);
        $buildingBlock->addStatistic(new BuildingBlockStatistics($buildingBlock, $campaign, $status));

        for ($number = 0; $number < $floors; ++$number) {
            $floor = new Floor($number, $buildingBlock);
            $floor->setCreatedByAdherent($createdBy ?? $this->getReference('adherent-20'));
            $floor->setCreatedAt($createdAt);
            $floor->addStatistic(new FloorStatistics($floor, $campaign, $status));
            $buildingBlock->addFloor($floor);
        }
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
