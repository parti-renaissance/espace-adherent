<?php

declare(strict_types=1);

namespace App\Entity\Pap;

use ApiPlatform\Metadata\ApiResource;
use App\Entity\EntityAdherentBlameableInterface;
use App\Entity\EntityAdherentBlameableTrait;
use App\Entity\EntityIdentityTrait;
use App\Entity\EntityTimestampableTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Attribute\Groups;

#[ApiResource(
    operations: [],
    normalizationContext: ['groups' => ['pap_building_block_list'], 'iri' => true],
    paginationEnabled: false
)]
#[ORM\Entity]
#[ORM\Table(name: 'pap_building_block')]
#[ORM\UniqueConstraint(name: 'building_block_unique', columns: ['name', 'building_id'])]
class BuildingBlock implements EntityAdherentBlameableInterface, CampaignStatisticsOwnerInterface
{
    use EntityAdherentBlameableTrait;
    use EntityIdentityTrait;
    use EntityTimestampableTrait;
    use CampaignStatisticsTrait;

    #[Groups(['pap_building_block_list'])]
    #[ORM\Column]
    private string $name;

    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: Building::class, inversedBy: 'buildingBlocks')]
    private Building $building;

    /**
     * @var Floor[]|Collection
     */
    #[Groups(['pap_building_block_list'])]
    #[ORM\OneToMany(mappedBy: 'buildingBlock', targetEntity: Floor::class, cascade: ['all'], orphanRemoval: true)]
    #[ORM\OrderBy(['number' => 'ASC'])]
    private Collection $floors;

    /**
     * @var BuildingBlockStatistics[]|Collection
     */
    #[ORM\OneToMany(mappedBy: 'buildingBlock', targetEntity: BuildingBlockStatistics::class, cascade: ['all'], orphanRemoval: true)]
    private Collection $statistics;

    public function __construct(string $name, Building $building, ?UuidInterface $uuid = null)
    {
        $this->uuid = $uuid ?? Uuid::uuid4();
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

    public function getFloorByNumber(int $number): ?Floor
    {
        $criteria = Criteria::create()
            ->andWhere(Criteria::expr()->eq('number', $number));

        return $this->floors->matching($criteria)->count() > 0
            ? $this->floors->matching($criteria)->first()
            : null;
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
