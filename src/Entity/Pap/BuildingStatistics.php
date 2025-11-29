<?php

declare(strict_types=1);

namespace App\Entity\Pap;

use ApiPlatform\Metadata\ApiResource;
use App\Entity\Adherent;
use App\Entity\EntityIdentityTrait;
use App\Entity\EntityTimestampableTrait;
use App\Pap\BuildingStatusEnum;
use App\Repository\Pap\BuildingStatisticsRepository;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Serializer\Attribute\Groups;

#[ApiResource(
    operations: [],
    normalizationContext: ['groups' => ['pap_building_statistics_read']],
    security: "is_granted('REQUEST_SCOPE_GRANTED', ['pap_v2', 'pap'])"
)]
#[ORM\Entity(repositoryClass: BuildingStatisticsRepository::class)]
#[ORM\Index(columns: ['status'])]
#[ORM\Table(name: 'pap_building_statistics')]
#[ORM\UniqueConstraint(columns: ['building_id', 'campaign_id'])]
class BuildingStatistics implements CampaignStatisticsInterface
{
    use EntityIdentityTrait;
    use EntityTimestampableTrait;
    use StatusTrait;

    #[Groups(['pap_building_statistics_read'])]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: Building::class, inversedBy: 'statistics')]
    private Building $building;

    #[Groups(['pap_address_list', 'pap_address_read'])]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: Campaign::class, inversedBy: 'buildingStatistics')]
    private Campaign $campaign;

    #[Groups(['pap_address_list', 'pap_address_read', 'pap_building_statistics_read'])]
    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTime $lastPassage = null;

    #[Groups(['pap_address_list', 'pap_address_read', 'pap_building_statistics_read'])]
    #[ORM\JoinColumn(onDelete: 'SET NULL')]
    #[ORM\ManyToOne(targetEntity: Adherent::class)]
    protected ?Adherent $lastPassageDoneBy;

    #[ORM\Column(type: 'smallint', options: ['unsigned' => true, 'default' => 0])]
    private int $nbVoters = 0;

    #[Groups(['pap_address_list', 'pap_address_read', 'pap_building_statistics_read'])]
    #[ORM\Column(type: 'smallint', options: ['unsigned' => true, 'default' => 0])]
    private int $nbVisitedDoors = 0;

    #[Groups(['pap_address_list', 'pap_address_read', 'pap_building_statistics_read'])]
    #[ORM\Column(type: 'smallint', options: ['unsigned' => true, 'default' => 0])]
    private int $nbDistributedPrograms = 0;

    #[Groups(['pap_address_list', 'pap_address_read'])]
    #[ORM\Column(type: 'smallint', options: ['unsigned' => true, 'default' => 0])]
    private int $nbSurveys = 0;

    public function __construct(Building $building, Campaign $campaign, ?string $status = null)
    {
        $this->building = $building;
        $this->campaign = $campaign;

        $this->uuid = Uuid::uuid4();
        $this->status = $status ?? BuildingStatusEnum::TODO;
    }

    public function getBuilding(): Building
    {
        return $this->building;
    }

    public function getCampaign(): Campaign
    {
        return $this->campaign;
    }

    public function getLastPassage(): ?\DateTimeInterface
    {
        return $this->lastPassage;
    }

    public function setLastPassage(?\DateTimeInterface $lastPassage): void
    {
        $this->lastPassage = $lastPassage;
    }

    public function getLastPassageDoneBy(): ?Adherent
    {
        return $this->lastPassageDoneBy;
    }

    public function setLastPassageDoneBy(?Adherent $lastPassageDoneBy): void
    {
        $this->lastPassageDoneBy = $lastPassageDoneBy;
    }

    public function getNbVoters(): int
    {
        return $this->nbVoters;
    }

    public function setNbVoters(int $nbVoters): void
    {
        $this->nbVoters = $nbVoters;
    }

    public function getNbVisitedDoors(): int
    {
        return $this->nbVisitedDoors;
    }

    public function setNbVisitedDoors(int $nbVisitedDoors): void
    {
        $this->nbVisitedDoors = $nbVisitedDoors;
    }

    public function getNbDistributedPrograms(): int
    {
        return $this->nbDistributedPrograms;
    }

    public function setNbDistributedPrograms(int $nbDistributedPrograms): void
    {
        $this->nbDistributedPrograms = $nbDistributedPrograms;
    }

    public function getNbSurveys(): int
    {
        return $this->nbSurveys;
    }

    public function setNbSurveys(int $nbSurveys): void
    {
        $this->nbSurveys = $nbSurveys;
    }

    public function getStatusDetail(): ?string
    {
        if ($this->statusDetail || BuildingStatusEnum::COMPLETED !== $this->status) {
            return $this->statusDetail;
        }

        return BuildingStatusEnum::COMPLETED_PAP;
    }
}
