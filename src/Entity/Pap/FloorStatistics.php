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
    use StatusTrait;

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
     * @ORM\Column(type="simple_array", nullable=true)
     */
    private array $doors = [];

    /**
     * @ORM\Column(type="smallint", options={"unsigned": true, "default": 0})
     */
    private int $nbSurveys = 0;

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

    public function getDoors(): array
    {
        return $this->doors;
    }

    public function setDoors(array $doors): void
    {
        $this->doors = $doors;
    }

    public function getNbSurveys(): int
    {
        return $this->nbSurveys;
    }

    public function setNbSurveys(int $nbSurveys): void
    {
        $this->nbSurveys = $nbSurveys;
    }
}
