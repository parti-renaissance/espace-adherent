<?php

declare(strict_types=1);

namespace App\Entity\Pap;

use App\Entity\EntityIdentityTrait;
use App\Entity\EntityTimestampableTrait;
use App\Pap\BuildingStatusEnum;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;

#[ORM\Entity]
#[ORM\Table(name: 'pap_floor_statistics')]
class FloorStatistics implements CampaignStatisticsInterface
{
    use EntityIdentityTrait;
    use EntityTimestampableTrait;
    use StatusTrait;

    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: Floor::class, inversedBy: 'statistics')]
    private Floor $floor;

    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: Campaign::class)]
    private Campaign $campaign;

    #[ORM\Column(type: 'simple_array', nullable: true)]
    private array $visitedDoors = [];

    #[ORM\Column(type: 'smallint', options: ['unsigned' => true, 'default' => 0])]
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

    public function getVisitedDoors(): array
    {
        return $this->visitedDoors;
    }

    public function setVisitedDoors(array $visitedDoors): void
    {
        $this->visitedDoors = $visitedDoors;
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
