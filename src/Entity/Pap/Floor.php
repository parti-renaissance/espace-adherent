<?php

namespace App\Entity\Pap;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Entity\EntityAdherentBlameableInterface;
use App\Entity\EntityAdherentBlameableTrait;
use App\Entity\EntityIdentityTrait;
use App\Entity\EntityTimestampableTrait;
use App\Pap\BuildingStatusEnum;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="pap_floor")
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
class Floor implements EntityAdherentBlameableInterface
{
    use EntityAdherentBlameableTrait;
    use EntityIdentityTrait;
    use EntityTimestampableTrait;

    /**
     * @Assert\NotNull
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Pap\BuildingBlock", inversedBy="floors")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private BuildingBlock $buildingBlock;

    /**
     * @ORM\Column
     *
     * @Groups({"pap_building_block_list"})
     */
    private int $number;

    /**
     * @ORM\Column(length=25)
     *
     * @Assert\Choice(
     *     callback={"App\Pap\BuildingBlockStatusEnum", "toArray"},
     *     strict=true
     * )
     *
     * @Groups({"pap_building_block_list"})
     */
    private string $status;

    public function __construct(int $number, BuildingBlock $buildingBlock)
    {
        $this->uuid = Uuid::uuid4();
        $this->number = $number;
        $this->buildingBlock = $buildingBlock;
        $this->status = BuildingStatusEnum::ONGOING;
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

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): void
    {
        $this->status = $status;
    }
}
