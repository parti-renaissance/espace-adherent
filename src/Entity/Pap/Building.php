<?php

declare(strict_types=1);

namespace App\Entity\Pap;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Put;
use App\Entity\EntityIdentityTrait;
use App\Pap\BuildingTypeEnum;
use App\Repository\Pap\BuildingRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiResource(
    operations: [
        new Get(
            uriTemplate: '/v3/pap/buildings/{uuid}',
            requirements: ['uuid' => '%pattern_uuid%'],
            security: "is_granted('ROLE_OAUTH_SCOPE_JEMARCHE_APP') and is_granted('ROLE_PAP_USER')"
        ),
        new Put(
            uriTemplate: '/v3/pap/buildings/{uuid}',
            requirements: ['uuid' => '%pattern_uuid%'],
            security: "is_granted('ROLE_OAUTH_SCOPE_JEMARCHE_APP') and is_granted('ROLE_PAP_USER')"
        ),
    ],
    normalizationContext: ['groups' => ['pap_building_read'], 'iri' => true],
    denormalizationContext: ['groups' => ['pap_building_write']]
)]
#[ORM\Entity(repositoryClass: BuildingRepository::class)]
#[ORM\Table(name: 'pap_building')]
class Building implements CampaignStatisticsOwnerInterface
{
    use EntityIdentityTrait;
    use CampaignStatisticsTrait;

    #[Assert\Choice(callback: [BuildingTypeEnum::class, 'toArray'], message: 'pap.building.type.invalid_choice')]
    #[Groups(['pap_address_list', 'pap_building_read', 'pap_building_write', 'pap_address_read', 'pap_building_statistics_read'])]
    #[ORM\Column(nullable: true)]
    private ?string $type = null;

    #[Groups(['pap_campaign_history_read_list', 'pap_building_statistics_read'])]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[ORM\OneToOne(inversedBy: 'building', targetEntity: Address::class)]
    private ?Address $address = null;

    /**
     * @var BuildingBlock[]|Collection
     */
    #[ORM\OneToMany(mappedBy: 'building', targetEntity: BuildingBlock::class, cascade: ['all'], orphanRemoval: true)]
    #[ORM\OrderBy(['name' => 'ASC'])]
    private Collection $buildingBlocks;

    /**
     * @var BuildingStatistics[]|Collection
     */
    #[ORM\OneToMany(mappedBy: 'building', targetEntity: BuildingStatistics::class, cascade: ['all'], fetch: 'EXTRA_LAZY', orphanRemoval: true)]
    private Collection $statistics;

    #[ORM\ManyToOne(targetEntity: Campaign::class)]
    private ?Campaign $currentCampaign = null;

    public function __construct(?UuidInterface $uuid = null)
    {
        $this->uuid = $uuid ?? Uuid::uuid4();
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

    /**
     * @return BuildingBlock[]|Collection
     */
    public function getBuildingBlocks(): Collection
    {
        return $this->buildingBlocks;
    }

    public function addBuildingBlock(BuildingBlock $buildingBlock): void
    {
        if (!$this->buildingBlocks->contains($buildingBlock)) {
            $buildingBlock->setBuilding($this);
            $this->buildingBlocks->add($buildingBlock);
        }
    }

    public function removeBuildingBlock(BuildingBlock $buildingBlock): void
    {
        $this->buildingBlocks->removeElement($buildingBlock);
    }

    public function getBuildingBlockByName(string $name): ?BuildingBlock
    {
        $criteria = Criteria::create()
            ->andWhere(Criteria::expr()->eq('name', $name))
        ;

        return $this->buildingBlocks->matching($criteria)->count() > 0
            ? $this->buildingBlocks->matching($criteria)->first()
            : null;
    }

    public function setCurrentCampaign(Campaign $campaign): void
    {
        $this->currentCampaign = $campaign;
    }

    public function getCurrentCampaign(): ?Campaign
    {
        return $this->currentCampaign;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): void
    {
        $this->type = $type;
    }

    #[Groups(['pap_address_list', 'pap_address_read'])]
    public function getCampaignStatistics(): ?CampaignStatisticsInterface
    {
        if ($this->currentCampaign) {
            return $this->findStatisticsForCampaign($this->currentCampaign) ?? new BuildingStatistics(
                $this,
                $this->currentCampaign
            );
        }

        return null;
    }
}
