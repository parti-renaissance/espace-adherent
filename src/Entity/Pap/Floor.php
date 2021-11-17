<?php

namespace App\Entity\Pap;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Entity\Adherent;
use App\Entity\EntityTimestampableTrait;
use App\Pap\BuildingStatusEnum;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
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
class Floor
{
    use EntityTimestampableTrait;

    /**
     * @ORM\Column(type="integer", options={"unsigned": true})
     * @ORM\Id
     * @ORM\GeneratedValue
     */
    private ?int $id = null;

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
     * @ORM\Column(length=10)
     *
     * @Assert\Choice(
     *     callback={"App\Pap\BuildingBlockStatusEnum", "toArray"},
     *     strict=true
     * )
     *
     * @Groups({"pap_building_block_list"})
     */
    private string $status;

    /**
     * @Gedmo\Blameable(on="create")
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Adherent")
     * @ORM\JoinColumn(onDelete="SET NULL")
     */
    private ?Adherent $createdBy = null;

    /**
     * @Gedmo\Blameable(on="update")
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Adherent")
     * @ORM\JoinColumn(onDelete="SET NULL")
     */
    private ?Adherent $updatedBy = null;

    public function __construct(int $number, BuildingBlock $buildingBlock)
    {
        $this->number = $number;
        $this->buildingBlock = $buildingBlock;
        $this->status = BuildingStatusEnum::ONGOING;
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getCreatedBy(): ?Adherent
    {
        return $this->createdBy;
    }

    public function setCreatedBy(?Adherent $createdBy): void
    {
        $this->createdBy = $createdBy;
    }

    public function getUpdatedBy(): ?Adherent
    {
        return $this->updatedBy;
    }

    public function setUpdatedBy(?Adherent $updatedBy): void
    {
        $this->updatedBy = $updatedBy;
    }
}
