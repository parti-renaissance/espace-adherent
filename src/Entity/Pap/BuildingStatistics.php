<?php

namespace App\Entity\Pap;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Entity\Adherent;
use App\Entity\EntityIdentityTrait;
use App\Entity\EntityTimestampableTrait;
use App\Pap\BuildingStatusEnum;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass="App\Repository\Pap\BuildingStatisticsRepository")
 * @ORM\Table(
 *     name="pap_building_statistics",
 *     uniqueConstraints={
 *         @ORM\UniqueConstraint(columns={"building_id", "campaign_id"}),
 *     },
 *     indexes={
 *         @ORM\Index(columns={"status"}),
 *     },
 * )
 *
 * @ApiResource(
 *     attributes={
 *         "security": "is_granted('IS_FEATURE_GRANTED', ['pap_v2', 'pap'])",
 *         "normalization_context": {
 *             "groups": {"pap_building_statistics_read"},
 *         },
 *     },
 *     collectionOperations={},
 *     itemOperations={},
 * )
 */
class BuildingStatistics implements CampaignStatisticsInterface
{
    use EntityIdentityTrait;
    use EntityTimestampableTrait;
    use StatusTrait;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Pap\Building", inversedBy="statistics")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    #[Groups(['pap_building_statistics_read'])]
    private Building $building;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Pap\Campaign", inversedBy="buildingStatistics")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    #[Groups(['pap_address_list', 'pap_address_read'])]
    private Campaign $campaign;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    #[Groups(['pap_address_list', 'pap_address_read', 'pap_building_statistics_read'])]
    private ?\DateTime $lastPassage = null;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Adherent")
     * @ORM\JoinColumn(onDelete="SET NULL")
     */
    #[Groups(['pap_address_list', 'pap_address_read', 'pap_building_statistics_read'])]
    protected ?Adherent $lastPassageDoneBy;

    /**
     * @ORM\Column(type="smallint", options={"unsigned": true, "default": 0})
     */
    private int $nbVoters = 0;

    /**
     * @ORM\Column(type="smallint", options={"unsigned": true, "default": 0})
     */
    #[Groups(['pap_address_list', 'pap_address_read', 'pap_building_statistics_read'])]
    private int $nbVisitedDoors = 0;

    /**
     * @ORM\Column(type="smallint", options={"unsigned": true, "default": 0})
     */
    #[Groups(['pap_address_list', 'pap_address_read', 'pap_building_statistics_read'])]
    private int $nbDistributedPrograms = 0;

    /**
     * @ORM\Column(type="smallint", options={"unsigned": true, "default": 0})
     */
    #[Groups(['pap_address_list', 'pap_address_read'])]
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
