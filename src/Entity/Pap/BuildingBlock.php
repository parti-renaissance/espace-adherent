<?php

namespace App\Entity\Pap;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Entity\Adherent;
use App\Entity\EntityTimestampableTrait;
use App\Pap\BuildingStatusEnum;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

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
class BuildingBlock
{
    use EntityTimestampableTrait;

    /**
     * @ORM\Column(type="integer", options={"unsigned": true})
     * @ORM\Id
     * @ORM\GeneratedValue
     */
    private ?int $id;

    /**
     * @ORM\Column
     *
     * @Groups({"pap_building_block_list"})
     */
    private string $name;

    /**
     * @ORM\Column(length=10)
     *
     * @Assert\Choice(
     *     callback={"App\Pap\BuildingStatusEnum", "toArray"},
     *     strict=true
     * )
     *
     * @Groups({"pap_building_block_list"})
     */
    private ?string $status;

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
     * @Groups({"pap_building_block_list"})
     */
    private Collection $floors;

    /**
     * @Gedmo\Blameable(on="create")
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Adherent")
     * @ORM\JoinColumn(onDelete="SET NULL")
     */
    private ?Adherent $createdBy;

    /**
     * @Gedmo\Blameable(on="update")
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Adherent")
     * @ORM\JoinColumn(onDelete="SET NULL")
     */
    private ?Adherent $updatedBy;

    public function __construct(string $name, Building $building)
    {
        $this->name = $name;
        $this->building = $building;
        $this->status = BuildingStatusEnum::ONGOING;
        $this->floors = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): void
    {
        $this->status = $status;
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
