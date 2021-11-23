<?php

namespace App\Entity\Pap;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Entity\EntityAdherentBlameableInterface;
use App\Entity\EntityAdherentBlameableTrait;
use App\Entity\EntityIdentityTrait;
use App\Entity\EntityTimestampableTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity
 * @ORM\Table(name="pap_building_block")
 *
 * @ApiResource(
 *     attributes={
 *         "normalization_context": {
 *             "groups": {"pap_building_block_list"},
 *             "iri": true,
 *         },
 *         "pagination_enabled": false,
 *     },
 *     collectionOperations={},
 *     itemOperations={},
 * )
 */
class BuildingBlock implements EntityAdherentBlameableInterface
{
    use EntityAdherentBlameableTrait;
    use EntityIdentityTrait;
    use EntityTimestampableTrait;
    use CampaignStatisticsTrait;

    /**
     * @ORM\Column
     *
     * @Groups({"pap_address_list", "pap_building_block_list"})
     */
    private string $name;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Pap\Building", inversedBy="buildingBlocks")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private Building $building;

    /**
     * @var Floor[]|Collection
     *
     * @ORM\OneToMany(
     *     targetEntity="App\Entity\Pap\Floor",
     *     mappedBy="buildingBlock",
     *     cascade={"all"},
     *     orphanRemoval=true,
     *     fetch="EAGER"
     * )
     * @ORM\OrderBy({"number": "ASC"})
     *
     * @Groups({"pap_address_list", "pap_building_block_list"})
     */
    private Collection $floors;

    /**
     * @var BuildingBlockStatistics[]|Collection
     *
     * @ORM\OneToMany(
     *     targetEntity="App\Entity\Pap\BuildingBlockStatistics",
     *     mappedBy="buildingBlock",
     *     cascade={"all"},
     *     orphanRemoval=true
     * )
     */
    private Collection $statistics;

    public function __construct(string $name, Building $building)
    {
        $this->uuid = Uuid::uuid4();
        $this->name = $name;
        $this->building = $building;
        $this->floors = new ArrayCollection();
        $this->statistics = new ArrayCollection();
    }

    public function getBuilding(): Building
    {
        return $this->building;
    }

    public function setBuilding(Building $building): void
    {
        $this->building = $building;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return Floor[]|Collection
     */
    public function getFloors(): Collection
    {
        return $this->floors;
    }

    public function addFloor(Floor $floor): void
    {
        if (!$this->floors->contains($floor)) {
            $floor->setBuildingBlock($this);
            $this->floors->add($floor);
        }
    }

    public function removeFloor(Floor $floor): void
    {
        $this->floors->removeElement($floor);
    }
}
