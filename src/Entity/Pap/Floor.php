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
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\Pap\FloorRepository")
 * @ORM\Table(name="pap_floor", uniqueConstraints={
 *     @ORM\UniqueConstraint(name="floor_unique", columns={"number", "building_block_id"})
 * })
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
class Floor implements EntityAdherentBlameableInterface, CampaignStatisticsOwnerInterface
{
    use EntityAdherentBlameableTrait;
    use EntityIdentityTrait;
    use EntityTimestampableTrait;
    use CampaignStatisticsTrait;

    /**
     * @Assert\NotNull
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Pap\BuildingBlock", inversedBy="floors")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private BuildingBlock $buildingBlock;

    /**
     * @ORM\Column(type="smallint", options={"unsigned": true})
     *
     * @Groups({"pap_building_block_list"})
     */
    private int $number;

    /**
     * @var FloorStatistics[]|Collection
     *
     * @ORM\OneToMany(
     *     targetEntity="FloorStatistics",
     *     mappedBy="floor",
     *     cascade={"all"},
     *     orphanRemoval=true
     * )
     */
    private Collection $statistics;

    public function __construct(int $number, BuildingBlock $buildingBlock, UuidInterface $uuid = null)
    {
        $this->uuid = $uuid ?? Uuid::uuid4();
        $this->number = $number;
        $this->buildingBlock = $buildingBlock;
        $this->statistics = new ArrayCollection();
    }

    public function getBuildingBlock(): BuildingBlock
    {
        return $this->buildingBlock;
    }

    public function setBuildingBlock(BuildingBlock $buildingBlock): void
    {
        $this->buildingBlock = $buildingBlock;
    }

    public function getNumber(): int
    {
        return $this->number;
    }

    public function setNumber(int $number): void
    {
        $this->number = $number;
    }
}
