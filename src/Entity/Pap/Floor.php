<?php

declare(strict_types=1);

namespace App\Entity\Pap;

use ApiPlatform\Metadata\ApiResource;
use App\Entity\EntityAdherentBlameableInterface;
use App\Entity\EntityAdherentBlameableTrait;
use App\Entity\EntityIdentityTrait;
use App\Entity\EntityTimestampableTrait;
use App\Repository\Pap\FloorRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiResource(
    operations: [],
    normalizationContext: ['groups' => ['pap_building_block_list'], 'iri' => true],
    paginationEnabled: false
)]
#[ORM\Entity(repositoryClass: FloorRepository::class)]
#[ORM\Table(name: 'pap_floor')]
#[ORM\UniqueConstraint(name: 'floor_unique', columns: ['number', 'building_block_id'])]
class Floor implements EntityAdherentBlameableInterface, CampaignStatisticsOwnerInterface
{
    use EntityAdherentBlameableTrait;
    use EntityIdentityTrait;
    use EntityTimestampableTrait;
    use CampaignStatisticsTrait;

    #[Assert\NotNull]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: BuildingBlock::class, inversedBy: 'floors')]
    private BuildingBlock $buildingBlock;

    #[Groups(['pap_building_block_list'])]
    #[ORM\Column(type: 'smallint', options: ['unsigned' => true])]
    private int $number;

    /**
     * @var FloorStatistics[]|Collection
     */
    #[ORM\OneToMany(mappedBy: 'floor', targetEntity: FloorStatistics::class, cascade: ['all'], orphanRemoval: true)]
    private Collection $statistics;

    public function __construct(int $number, BuildingBlock $buildingBlock, ?UuidInterface $uuid = null)
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
