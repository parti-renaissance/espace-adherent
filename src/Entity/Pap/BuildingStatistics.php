<?php

namespace App\Entity\Pap;

use App\Entity\Adherent;
use App\Entity\EntityIdentityTrait;
use App\Entity\EntityTimestampableTrait;
use App\Pap\BuildingStatusEnum;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity
 * @ORM\Table(name="pap_building_statistics")
 */
class BuildingStatistics implements CampaignStatisticsInterface
{
    use EntityIdentityTrait;
    use EntityTimestampableTrait;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Pap\Building", inversedBy="statistics")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private Building $building;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Pap\Campaign")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     *
     * @Groups({"pap_address_list"})
     */
    private Campaign $campaign;

    /**
     * @ORM\Column(length=25)
     *
     * @Groups({"pap_address_list"})
     */
    private string $status;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     *
     * @Groups({"pap_address_list"})
     */
    private ?\DateTime $lastPassage = null;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Adherent")
     * @ORM\JoinColumn(onDelete="SET NULL")
     *
     * @Groups({"pap_address_list"})
     */
    protected ?Adherent $lastPassageDoneBy;

    /**
     * @ORM\Column(type="smallint", options={"unsigned": true, "default": 0})
     */
    private int $nbVoters = 0;

    /**
     * @ORM\Column(type="smallint", options={"unsigned": true, "default": 0})
     *
     * @Groups({"pap_address_list"})
     */
    private int $nbDoors = 0;

    /**
     * @ORM\Column(type="smallint", options={"unsigned": true, "default": 0})
     *
     * @Groups({"pap_address_list"})
     */
    private int $nbSurveys = 0;

    public function __construct(Building $building, Campaign $campaign)
    {
        $this->building = $building;
        $this->campaign = $campaign;

        $this->uuid = Uuid::uuid4();
        $this->status = BuildingStatusEnum::ONGOING;
    }

    public function getBuilding(): Building
    {
        return $this->building;
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

    public function getNbDoors(): int
    {
        return $this->nbDoors;
    }

    public function setNbDoors(int $nbDoors): void
    {
        $this->nbDoors = $nbDoors;
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
