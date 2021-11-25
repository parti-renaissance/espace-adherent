<?php

namespace App\Entity\Pap;

use App\Entity\EntityIdentityTrait;
use App\Entity\EntityTimestampableTrait;
use App\Pap\BuildingStatusEnum;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;

/**
 * @ORM\Entity
 * @ORM\Table(name="pap_building_block_statistics")
 */
class BuildingBlockStatistics implements CampaignStatisticsInterface
{
    use EntityIdentityTrait;
    use EntityTimestampableTrait;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Pap\BuildingBlock", inversedBy="statistics")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private BuildingBlock $buildingBlock;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Pap\Campaign")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private Campaign $campaign;

    /**
     * @ORM\Column(length=25)
     */
    private string $status;

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

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): void
    {
        $this->status = $status;
    }
}
