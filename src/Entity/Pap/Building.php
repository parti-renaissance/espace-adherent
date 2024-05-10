<?php

namespace App\Entity\Pap;

use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Annotation\ApiSubresource;
use App\Entity\EntityIdentityTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\Pap\BuildingRepository")
 * @ORM\Table(name="pap_building")
 *
 * @ApiResource(
 *     attributes={
 *         "normalization_context": {
 *             "groups": {"pap_building_read"},
 *             "iri": true,
 *         },
 *         "denormalization_context": {
 *             "groups": {"pap_building_write"},
 *         },
 *     },
 *     itemOperations={
 *         "get": {
 *             "path": "/v3/pap/buildings/{uuid}",
 *             "requirements": {"uuid": "%pattern_uuid%"},
 *             "security": "is_granted('ROLE_OAUTH_SCOPE_JEMARCHE_APP') and is_granted('ROLE_PAP_USER')",
 *         },
 *         "put": {
 *             "path": "/v3/pap/buildings/{uuid}",
 *             "requirements": {"uuid": "%pattern_uuid%"},
 *             "security": "is_granted('ROLE_OAUTH_SCOPE_JEMARCHE_APP') and is_granted('ROLE_PAP_USER')",
 *         },
 *     },
 * )
 */
class Building implements CampaignStatisticsOwnerInterface
{
    use EntityIdentityTrait;
    use CampaignStatisticsTrait;

    /**
     * @ORM\Column(nullable=true)
     *
     * @Assert\Choice(
     *     callback={"App\Pap\BuildingTypeEnum", "toArray"},
     *     message="pap.building.type.invalid_choice"
     * )
     *
     * @Groups({
     *     "pap_address_list",
     *     "pap_building_read",
     *     "pap_building_write",
     *     "pap_address_read",
     *     "pap_building_statistics_read",
     * })
     */
    private ?string $type = null;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Pap\Address", inversedBy="building")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     *
     * @Groups({
     *     "pap_campaign_history_read_list",
     *     "pap_building_statistics_read",
     * })
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

    /**
     * @Groups({"pap_address_list", "pap_address_read"})
     */
    public function getCampaignStatistics(): ?CampaignStatisticsInterface
    {
        return $this->currentCampaign ? $this->findStatisticsForCampaign($this->currentCampaign) : new BuildingStatistics($this, $this->currentCampaign);
    }
}
