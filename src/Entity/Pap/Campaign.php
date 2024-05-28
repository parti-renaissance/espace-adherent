<?php

namespace App\Entity\Pap;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Annotation\ApiSubresource;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use App\Api\Filter\ScopeVisibilityFilter;
use App\Collection\ZoneCollection;
use App\Entity\Adherent;
use App\Entity\EntityAdherentBlameableInterface;
use App\Entity\EntityAdherentBlameableTrait;
use App\Entity\EntityAdministratorTrait;
use App\Entity\EntityIdentityTrait;
use App\Entity\EntityScopeVisibilityWithZonesInterface;
use App\Entity\EntityTimestampableTrait;
use App\Entity\EntityZoneTrait;
use App\Entity\IndexableEntityInterface;
use App\Entity\Jecoute\Survey;
use App\Firebase\DynamicLinks\DynamicLinkObjectInterface;
use App\Firebase\DynamicLinks\DynamicLinkObjectTrait;
use App\Scope\ScopeVisibilityEnum;
use App\Validator\PapCampaignStarted as AssertStartedPapCampaignValid;
use App\Validator\PapCampaignVotePlaces as AssertVotePlacesValid;
use App\Validator\Scope\ScopeVisibility;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\Pap\CampaignRepository")
 * @ORM\Table(name="pap_campaign", indexes={
 *     @ORM\Index(columns={"begin_at", "finish_at"}),
 * })
 *
 * @ORM\EntityListeners({
 *     "App\EntityListener\DynamicLinkListener",
 *     "App\EntityListener\AlgoliaIndexListener",
 *     "App\EntityListener\PapCampaignListener",
 * })
 *
 * @ApiResource(
 *     shortName="PapCampaign",
 *     attributes={
 *         "order": {"createdAt": "DESC"},
 *         "pagination_client_enabled": true,
 *         "security": "is_granted('IS_FEATURE_GRANTED', ['pap_v2', 'pap']) or (is_granted('ROLE_OAUTH_SCOPE_JEMARCHE_APP') and is_granted('ROLE_PAP_USER'))",
 *         "normalization_context": {
 *             "iri": true,
 *             "groups": {"pap_campaign_read"},
 *         },
 *         "denormalization_context": {
 *             "groups": {"pap_campaign_write"}
 *         },
 *     },
 *     itemOperations={
 *         "get": {
 *             "method": "GET",
 *             "path": "/v3/pap_campaigns/{uuid}",
 *             "requirements": {"uuid": "%pattern_uuid%"},
 *         },
 *         "put": {
 *             "path": "/v3/pap_campaigns/{uuid}",
 *             "requirements": {"uuid": "%pattern_uuid%"},
 *             "security": "is_granted('IS_FEATURE_GRANTED', ['pap_v2', 'pap']) and is_granted('SCOPE_CAN_MANAGE', object)",
 *             "normalization_context": {"groups": {"pap_campaign_read_after_write"}},
 *         },
 *         "get_questioners_with_scores": {
 *             "method": "GET",
 *             "path": "/v3/pap_campaigns/{uuid}/questioners",
 *             "requirements": {"uuid": "%pattern_uuid%"},
 *             "security": "is_granted('IS_FEATURE_GRANTED', ['pap_v2', 'pap'])",
 *             "controller": "App\Controller\Api\Pap\GetPapCampaignQuestionersStatsController",
 *             "defaults": {"_api_receive": false},
 *         },
 *         "delete": {
 *             "path": "/v3/pap_campaigns/{uuid}",
 *             "requirements": {"uuid": "%pattern_uuid%"},
 *             "security": "is_granted('IS_FEATURE_GRANTED', ['pap_v2', 'pap']) and is_granted('CAN_DELETE_PAP_CAMPAIGN', object)",
 *         },
 *     },
 *     collectionOperations={
 *         "get": {
 *             "method": "GET",
 *             "path": "/v3/pap_campaigns",
 *             "normalization_context": {
 *                 "groups": {"pap_campaign_read_list"},
 *             },
 *         },
 *         "post": {
 *             "path": "/v3/pap_campaigns",
 *             "security": "is_granted('IS_FEATURE_GRANTED', ['pap_v2', 'pap'])",
 *             "normalization_context": {"groups": {"pap_campaign_read_after_write"}},
 *             "validation_groups": {"Default", "pap_campaign_creation"},
 *         },
 *         "get_kpi": {
 *             "method": "GET",
 *             "path": "/v3/pap_campaigns/kpi",
 *             "controller": "App\Controller\Api\Pap\GetPapCampaignsKpiController",
 *             "security": "is_granted('IS_FEATURE_GRANTED', ['pap_v2', 'pap'])",
 *         },
 *         "get_campaign_building_statistics": {
 *             "method": "GET",
 *             "path": "/v3/pap_campaigns/{uuid}/building_statistics",
 *             "requirements": {"uuid": "%pattern_uuid%"},
 *             "controller": "App\Controller\Api\Pap\GetCampaignBuildingStatisticsController",
 *             "security": "is_granted('IS_FEATURE_GRANTED', ['pap_v2', 'pap'])",
 *             "normalization_context": {
 *                 "groups": {"pap_building_statistics_read"},
 *             },
 *         },
 *         "get_campaign_vote_places": {
 *             "method": "GET",
 *             "path": "/v3/pap_campaigns/{uuid}/vote_places",
 *             "requirements": {"uuid": "%pattern_uuid%"},
 *             "controller": "App\Controller\Api\Pap\GetCampaignVotePlacesController",
 *             "pagination_enabled": false,
 *             "security": "is_granted('IS_FEATURE_GRANTED', ['pap_v2', 'pap'])",
 *             "normalization_context": {"iri": true, "groups": {"pap_vote_place_read"}},
 *         },
 *         "get_available_vote_places": {
 *             "method": "GET",
 *             "path": "/v3/pap_campaigns/{uuid}/available_vote_places",
 *             "requirements": {"uuid": "%pattern_uuid%"},
 *             "controller": "App\Controller\Api\Pap\GetAvailableVotePlaceForCampaignController",
 *             "normalization_context": {
 *                 "iri": true,
 *                 "groups": {"pap_vote_place_read"},
 *             },
 *             "security": "is_granted('IS_FEATURE_GRANTED', ['pap_v2', 'pap'])",
 *         },
 *     },
 * )
 *
 * @ApiFilter(ScopeVisibilityFilter::class)
 * @ApiFilter(SearchFilter::class, properties={
 *     "visibility": "exact",
 * })
 *
 * @ScopeVisibility
 * @AssertStartedPapCampaignValid
 * @AssertVotePlacesValid
 */
class Campaign implements IndexableEntityInterface, EntityScopeVisibilityWithZonesInterface, EntityAdherentBlameableInterface, DynamicLinkObjectInterface
{
    use EntityIdentityTrait;
    use EntityTimestampableTrait;
    use EntityAdministratorTrait;
    use EntityAdherentBlameableTrait;
    use EntityZoneTrait;
    use DynamicLinkObjectTrait;

    /**
     * @var string|null
     *
     * @ORM\Column
     *
     * @Assert\NotBlank
     * @Assert\Length(max=255)
     */
    #[Groups(['pap_campaign_read', 'pap_campaign_read_list', 'pap_campaign_write', 'pap_campaign_read_after_write'])]
    private $title;

    /**
     * @var string|null
     *
     * @ORM\Column(type="text", nullable=true)
     */
    #[Groups(['pap_campaign_read', 'pap_campaign_read_list', 'pap_campaign_write', 'pap_campaign_read_after_write'])]
    private $brief;

    /**
     * @var int|null
     *
     * @ORM\Column(type="integer")
     *
     * @Assert\NotBlank
     * @Assert\GreaterThan(value="0")
     */
    #[Groups(['pap_campaign_read', 'pap_campaign_read_list', 'pap_campaign_write', 'pap_campaign_read_after_write'])]
    private $goal;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(type="datetime", nullable=true)
     *
     * @Assert\NotBlank(groups={"regular_campaign"})
     * @Assert\GreaterThanOrEqual(
     *     value="today",
     *     message="pap.campaign.invalid_start_date",
     *     groups={"pap_campaign_creation"}
     * )
     */
    #[Groups(['pap_campaign_read', 'pap_campaign_read_list', 'pap_campaign_write', 'pap_campaign_read_after_write'])]
    private $beginAt;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(type="datetime", nullable=true)
     *
     * @Assert\NotBlank(groups={"regular_campaign"})
     * @Assert\Expression("value > this.getBeginAt()", message="pap.campaign.invalid_end_date")
     */
    #[Groups(['pap_campaign_read', 'pap_campaign_read_list', 'pap_campaign_write', 'pap_campaign_read_after_write'])]
    private $finishAt;

    /**
     * @var Survey|null
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Jecoute\Survey")
     * @ORM\JoinColumn(nullable=false)
     *
     * @Assert\NotBlank
     *
     * @ApiSubresource
     */
    #[Groups(['pap_campaign_write', 'pap_campaign_read', 'pap_campaign_read_after_write'])]
    private $survey;

    /**
     * @ORM\Column(type="integer", options={"unsigned": true, "default": 0})
     */
    private int $nbAddresses;

    /**
     * @ORM\Column(type="integer", options={"unsigned": true, "default": 0})
     */
    private int $nbVoters;

    /**
     * @var Collection|CampaignHistory[]
     *
     * @ORM\OneToMany(targetEntity="App\Entity\Pap\CampaignHistory", mappedBy="campaign", fetch="EXTRA_LAZY")
     */
    private $campaignHistories;

    /**
     * @var VotePlace[]|Collection
     *
     * @ORM\ManyToMany(targetEntity="App\Entity\Pap\VotePlace", inversedBy="campaigns")
     * @ORM\JoinTable(name="pap_campaign_vote_place")
     *
     * @ApiSubresource
     */
    #[Groups(['pap_campaign_write'])]
    private $votePlaces;

    /**
     * @var Collection|BuildingStatistics[]
     *
     * @ORM\OneToMany(targetEntity="App\Entity\Pap\BuildingStatistics", mappedBy="campaign", fetch="EXTRA_LAZY")
     *
     * @ApiSubresource
     */
    private Collection $buildingStatistics;

    /**
     * @ORM\Column(type="boolean", options={"default": false})
     */
    private bool $associated = false;

    /**
     * @ORM\Column(type="boolean", options={"default": true})
     */
    #[Groups(['pap_campaign_write', 'pap_campaign_read', 'pap_campaign_read_after_write', 'pap_campaign_read_list'])]
    private bool $enabled;

    /**
     * @ORM\Column(length=30)
     *
     * @Assert\NotBlank(message="scope.visibility.not_blank")
     * @Assert\Choice(choices=App\Scope\ScopeVisibilityEnum::ALL, message="scope.visibility.choice")
     */
    #[Groups(['pap_campaign_read', 'pap_campaign_read_list', 'pap_campaign_read_after_write'])]
    protected string $visibility = ScopeVisibilityEnum::NATIONAL;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Geo\Zone", cascade={"persist"})
     * @ORM\JoinTable(name="pap_campaign_zone")
     */
    protected Collection $zones;

    public function __construct(
        ?UuidInterface $uuid = null,
        ?string $title = null,
        ?string $brief = null,
        ?Survey $survey = null,
        ?int $goal = null,
        ?\DateTime $beginAt = null,
        ?\DateTime $finishAt = null,
        int $nbAddresses = 0,
        int $nbVoters = 0,
        array $zones = [],
        ?Adherent $createdByAdherent = null,
        bool $enabled = true
    ) {
        $this->uuid = $uuid ?? Uuid::uuid4();
        $this->title = $title;
        $this->brief = $brief;
        $this->survey = $survey;
        $this->goal = $goal;
        $this->beginAt = $beginAt;
        $this->finishAt = $finishAt;
        $this->nbAddresses = $nbAddresses;
        $this->nbVoters = $nbVoters;
        $this->createdByAdherent = $createdByAdherent;
        $this->enabled = $enabled;

        $this->campaignHistories = new ArrayCollection();
        $this->votePlaces = new ArrayCollection();
        $this->buildingStatistics = new ArrayCollection();

        $this->zones = new ZoneCollection();
        if ($zones) {
            $this->visibility = ScopeVisibilityEnum::LOCAL;
            $this->setZones($zones);
        }
    }

    public function __toString(): string
    {
        return (string) $this->title;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): void
    {
        $this->title = $title;
    }

    public function getBrief(): ?string
    {
        return $this->brief;
    }

    public function setBrief(?string $brief): void
    {
        $this->brief = $brief;
    }

    public function getGoal(): ?int
    {
        return $this->goal;
    }

    public function setGoal(?int $goal): void
    {
        $this->goal = $goal;
    }

    public function getBeginAt(): ?\DateTimeInterface
    {
        return $this->beginAt;
    }

    public function setBeginAt(?\DateTime $beginAt): void
    {
        $this->beginAt = $beginAt;
    }

    public function getFinishAt(): ?\DateTimeInterface
    {
        return $this->finishAt;
    }

    public function setFinishAt(?\DateTime $finishAt): void
    {
        $this->finishAt = $finishAt;
    }

    public function getSurvey(): ?Survey
    {
        return $this->survey;
    }

    public function setSurvey(Survey $survey): void
    {
        $this->survey = $survey;
    }

    public function isFinished(): bool
    {
        return null !== $this->finishAt && $this->finishAt <= new \DateTime();
    }

    public function getNbAddresses(): int
    {
        return $this->nbAddresses;
    }

    public function setNbAddresses(int $nbAddresses): void
    {
        $this->nbAddresses = $nbAddresses;
    }

    public function getNbVoters(): int
    {
        return $this->nbVoters;
    }

    public function setNbVoters(int $nbVoters): void
    {
        $this->nbVoters = $nbVoters;
    }

    /**
     * @return CampaignHistory[]|Collection
     */
    public function getCampaignHistoriesWithDataSurvey(): Collection
    {
        return $this->campaignHistories->filter(function (CampaignHistory $campaignHistory) {
            return $campaignHistory->getDataSurvey();
        });
    }

    public function getCampaignHistoriesToJoin(): Collection
    {
        return $this->campaignHistories->filter(function (CampaignHistory $campaignHistory) {
            return $campaignHistory->isToJoin();
        });
    }

    public function getCampaignHistoriesDoorOpen(): Collection
    {
        return $this->campaignHistories->filter(function (CampaignHistory $campaignHistory) {
            return $campaignHistory->isDoorOpenStatus();
        });
    }

    public function getCampaignHistoriesContactLater(): Collection
    {
        return $this->campaignHistories->filter(function (CampaignHistory $campaignHistory) {
            return $campaignHistory->isContactLaterStatus();
        });
    }

    public function addVotePlace(VotePlace $votePlace): void
    {
        if (!$this->votePlaces->contains($votePlace)) {
            $this->votePlaces->add($votePlace);
        }
    }

    public function removeVotePlace(VotePlace $votePlace): void
    {
        $this->votePlaces->removeElement($votePlace);
    }

    public function getVotePlaces(): Collection
    {
        return $this->votePlaces;
    }

    public function setVotePlaces(array $votePlaces): void
    {
        $this->votePlaces = $votePlaces;
    }

    public function getBuildingStatistics(): Collection
    {
        return $this->buildingStatistics;
    }

    public function getIndexOptions(): array
    {
        return [];
    }

    public function isIndexable(): bool
    {
        return true;
    }

    public function setAssociated(bool $value): void
    {
        $this->associated = $value;
    }

    public function getDynamicLinkPath(): string
    {
        return '/pap-campaigns/'.$this->uuid;
    }

    public function withSocialMeta(): bool
    {
        return true;
    }

    public function getSocialTitle(): string
    {
        return (string) $this->getTitle();
    }

    public function setVisibility(string $visibility): void
    {
        $this->visibility = $visibility;
    }

    public function getVisibility(): string
    {
        return $this->visibility;
    }

    public function isNationalVisibility(): bool
    {
        return ScopeVisibilityEnum::NATIONAL === $this->visibility;
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function setEnabled(bool $enabled): void
    {
        $this->enabled = $enabled;
    }

    public function getCreator(): string
    {
        return null !== $this->createdByAdherent ? $this->createdByAdherent->getFullName() : 'Admin';
    }
}
