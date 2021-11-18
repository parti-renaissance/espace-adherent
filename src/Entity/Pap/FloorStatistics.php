<?php

namespace App\Entity\Pap;

use App\Entity\EntityIdentityTrait;
use App\Entity\EntityTimestampableTrait;
use App\Pap\BuildingStatusEnum;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;

/**
 * @ORM\Entity
 * @ORM\Table(name="pap_floor_statistics")
 */
class FloorStatistics implements CampaignStatisticsInterface
{
    use EntityIdentityTrait;
    use EntityTimestampableTrait;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Pap\Floor", inversedBy="statistics")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private Floor $floor;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Pap\Campaign")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private Campaign $campaign;

    /**
     * @ORM\Column(length=25)
     */
    private string $status;

    public function __construct(Floor $floor, Campaign $campaign, ?string $status = null)
    {
        $this->floor = $floor;
        $this->campaign = $campaign;

        $this->uuid = Uuid::uuid4();
        $this->status = $status ?? BuildingStatusEnum::ONGOING;
    }

    public function getFloor(): Floor
    {
        return $this->floor;
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
