<?php

declare(strict_types=1);

namespace App\Entity\Pap;

use App\Entity\EntityIdentityTrait;
use App\Entity\EntityTimestampableTrait;
use App\Pap\BuildingStatusEnum;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;

#[ORM\Entity]
#[ORM\Table(name: 'pap_building_block_statistics')]
class BuildingBlockStatistics implements CampaignStatisticsInterface
{
    use EntityIdentityTrait;
    use EntityTimestampableTrait;
    use StatusTrait;

    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: BuildingBlock::class, inversedBy: 'statistics')]
    private BuildingBlock $buildingBlock;

    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: Campaign::class)]
    private Campaign $campaign;

    public function __construct(BuildingBlock $buildingBlock, Campaign $campaign, ?string $status = null)
    {
        $this->buildingBlock = $buildingBlock;
        $this->campaign = $campaign;

        $this->uuid = Uuid::uuid4();
        $this->status = $status ?? BuildingStatusEnum::ONGOING;
    }

    public function getBuildingBlock(): BuildingBlock
    {
        return $this->buildingBlock;
    }

    public function getCampaign(): Campaign
    {
        return $this->campaign;
    }
}
