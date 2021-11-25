<?php

namespace App\Entity\Pap;

use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Annotation\ApiSubresource;
use App\Entity\EntityIdentityTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity
 * @ORM\Table(name="pap_building")
 *
 * @ApiResource(
 *     subresourceOperations={
 *         "building_blocks_get_subresource": {
 *             "method": "GET",
 *             "path": "/v3/pap/buildings/{id}/building_blocks",
 *             "requirements": {"id": "%pattern_uuid%"},
 *         },
 *     },
 * )
 */
class Building
{
    use EntityIdentityTrait;
    use CampaignStatisticsTrait;

    /**
     * @ORM\Column(nullable=true)
     *
     * @Groups({"pap_address_list"})
     */
    private ?string $type = null;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Pap\Address", inversedBy="building")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private ?Address $address = null;

    /**
     * @var BuildingBlock[]|Collection
     *
     * @ApiSubresource
     *
     * @ORM\OneToMany(
     *     targetEntity="App\Entity\Pap\BuildingBlock",
     *     mappedBy="building",
     *     cascade={"all"},
     *     orphanRemoval=true,
     * )
     * @ORM\OrderBy({"name": "ASC"})
     */
    private Collection $buildingBlocks;

    /**
     * @var BuildingStatistics[]|Collection
     *
     * @ORM\OneToMany(
     *     targetEntity="App\Entity\Pap\BuildingStatistics",
     *     mappedBy="building",
     *     cascade={"all"},
     *     orphanRemoval=true,
     *     fetch="EXTRA_LAZY",
     * )
     */
    private Collection $statistics;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Pap\Campaign")
     */
    private ?Campaign $currentCampaign = null;

    public function __construct(UuidInterface $uuid = null)
    {
        $this->uuid = $uuid;
        $this->buildingBlocks = new ArrayCollection();
        $this->statistics = new ArrayCollection();
    }

    public function getAddress(): ?Address
    {
        return $this->address;
    }

    public function setAddress(Address $address): void
    {
        $this->address = $address;
    }

    public function addBuildingBlock(BuildingBlock $buildingBlock): void
    {
        if (!$this->buildingBlocks->contains($buildingBlock)) {
            $buildingBlock->setBuilding($this);
            $this->buildingBlocks->add($buildingBlock);
        }
    }

    public function setCurrentCampaign(Campaign $campaign): void
    {
        $this->currentCampaign = $campaign;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    /**
     * @Groups({"pap_address_list"})
     */
    public function getCampaignStatistics(): ?CampaignStatisticsInterface
    {
        return $this->currentCampaign ? $this->findStatisticsForCampaign($this->currentCampaign) : null;
    }
}
