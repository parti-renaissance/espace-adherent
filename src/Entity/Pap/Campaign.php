<?php

namespace App\Entity\Pap;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Annotation\ApiSubresource;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use App\Api\Filter\ScopeVisibilityFilter;
use App\Entity\EntityAdministratorTrait;
use App\Entity\EntityIdentityTrait;
use App\Entity\EntityScopeVisibilityInterface;
use App\Entity\EntityScopeVisibilityTrait;
use App\Entity\EntityTimestampableTrait;
use App\Entity\Geo\Zone;
use App\Entity\IndexableEntityInterface;
use App\Entity\Jecoute\Survey;
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
 * @ORM\EntityListeners({"App\EntityListener\AlgoliaIndexListener"})
 *
 * @ApiResource(
 *     shortName="PapCampaign",
 *     attributes={
 *         "order": {"createdAt": "DESC"},
 *         "pagination_client_enabled": true,
 *         "access_control": "is_granted('IS_FEATURE_GRANTED', 'pap') or (is_granted('ROLE_OAUTH_SCOPE_JEMARCHE_APP') and is_granted('ROLE_PAP_USER'))",
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
 *             "path": "/v3/pap_campaigns/{id}",
 *             "requirements": {"id": "%pattern_uuid%"},
 *         },
 *         "put": {
 *             "path": "/v3/pap_campaigns/{id}",
 *             "requirements": {"id": "%pattern_uuid%"},
 *             "access_control": "is_granted('IS_FEATURE_GRANTED', 'pap') and is_granted('SCOPE_CAN_MANAGE', object)",
 *             "normalization_context": {"groups": {"pap_campaign_read_after_write"}},
 *         },
 *         "get_questioners_with_scores": {
 *             "method": "GET",
 *             "path": "/v3/pap_campaigns/{uuid}/questioners",
 *             "requirements": {"uuid": "%pattern_uuid%"},
 *             "access_control": "is_granted('IS_FEATURE_GRANTED', 'pap')",
 *             "controller": "App\Controller\Api\Pap\GetPapCampaignQuestionersStatsController",
 *             "defaults": {"_api_receive": false},
 *         }
 *     },
 *     collectionOperations={
 *         "get": {
 *             "method": "GET",
 *             "path": "/v3/pap_campaigns",
 *         },
 *         "post": {
 *             "path": "/v3/pap_campaigns",
 *             "access_control": "is_granted('IS_FEATURE_GRANTED', 'pap')",
 *             "normalization_context": {"groups": {"pap_campaign_read_after_write"}},
 *         },
 *         "get_kpi": {
 *             "method": "GET",
 *             "path": "/v3/pap_campaigns/kpi",
 *             "controller": "App\Controller\Api\Pap\GetPapCampaignsKpiController",
 *             "access_control": "is_granted('IS_FEATURE_GRANTED', 'pap')",
 *         },
 *     },
 *     subresourceOperations={
 *         "survey_get_subresource": {
 *             "method": "GET",
 *             "path": "/v3/pap_campaigns/{id}/survey",
 *             "requirements": {"id": "%pattern_uuid%"},
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
 */
class Campaign implements IndexableEntityInterface, EntityScopeVisibilityInterface
{
    use EntityIdentityTrait;
    use EntityTimestampableTrait;
    use EntityAdministratorTrait;
    use EntityScopeVisibilityTrait;

    /**
     * @var string|null
     *
     * @ORM\Column
     *
     * @Assert\NotBlank
     * @Assert\Length(max=255)
     *
     * @Groups({"pap_campaign_read", "pap_campaign_write", "pap_campaign_read_after_write"})
     */
    private $title;

    /**
     * @var string|null
     *
     * @ORM\Column(type="text", nullable=true)
     *
     * @Groups({"pap_campaign_read", "pap_campaign_write", "pap_campaign_read_after_write"})
     */
    private $brief;

    /**
     * @var int|null
     *
     * @ORM\Column(type="integer")
     *
     * @Assert\NotBlank
     * @Assert\GreaterThan(value="0")
     *
     * @Groups({"pap_campaign_read", "pap_campaign_write", "pap_campaign_read_after_write"})
     */
    private $goal;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(type="datetime", nullable=true)
     *
     * @Assert\NotBlank(groups={"regular_campaign"})
     * @Assert\DateTime
     *
     * @Groups({"pap_campaign_read", "pap_campaign_write", "pap_campaign_read_after_write"})
     */
    private $beginAt;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(type="datetime", nullable=true)
     *
     * @Assert\NotBlank(groups={"regular_campaign"})
     * @Assert\DateTime
     *
     * @Groups({"pap_campaign_read", "pap_campaign_write", "pap_campaign_read_after_write"})
     */
    private $finishAt;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Jecoute\Survey")
     * @ORM\JoinColumn(nullable=false)
     *
     * @Assert\NotBlank
     *
     * @ApiSubresource
     *
     * @Groups({"pap_campaign_write", "pap_campaign_read_after_write"})
     */
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
     * @ORM\ManyToMany(targetEntity="App\Entity\Pap\VotePlace")
     * @ORM\JoinTable(name="pap_campaign_vote_place")
     */
    private $votePlaces;

    /**
     * @ORM\Column(name="delta_prediction_and_result_min_2017", type="float", nullable=true)
     */
    private ?float $deltaPredictionAndResultMin2017 = null;

    /**
     * @ORM\Column(name="delta_prediction_and_result_max_2017", type="float", nullable=true)
     */
    private ?float $deltaPredictionAndResultMax2017 = null;

    /**
     * @ORM\Column(name="delta_average_predictions_min", type="float", nullable=true)
     */
    private ?float $deltaAveragePredictionsMin = null;

    /**
     * @ORM\Column(name="delta_average_predictions_max", type="float", nullable=true)
     */
    private ?float $deltaAveragePredictionsMax = null;

    /**
     * @ORM\Column(name="abstentions_min_2017", type="float", nullable=true)
     */
    private ?float $abstentionsMin2017 = null;

    /**
     * @ORM\Column(name="abstentions_max_2017", type="float", nullable=true)
     */
    private ?float $abstentionsMax2017 = null;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private ?int $misregistrationsPriorityMin = null;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private ?int $misregistrationsPriorityMax = null;

    /**
     * @ORM\Column(type="boolean", options={"default": false})
     */
    private bool $associated = false;

    public function __construct(
        UuidInterface $uuid = null,
        string $title = null,
        string $brief = null,
        Survey $survey = null,
        int $goal = null,
        \DateTimeInterface $beginAt = null,
        \DateTimeInterface $finishAt = null,
        int $nbAddresses = 0,
        int $nbVoters = 0,
        Zone $zone = null
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

        $this->campaignHistories = new ArrayCollection();
        $this->votePlaces = new ArrayCollection();

        $this->setZone($zone);
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

    public function getBeginAt(): ?\DateTime
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

    public function getDeltaPredictionAndResultMin2017(): ?float
    {
        return $this->deltaPredictionAndResultMin2017;
    }

    public function setDeltaPredictionAndResultMin2017(?float $deltaPredictionAndResultMin2017): void
    {
        $this->deltaPredictionAndResultMin2017 = $deltaPredictionAndResultMin2017;
    }

    public function getDeltaPredictionAndResultMax2017(): ?float
    {
        return $this->deltaPredictionAndResultMax2017;
    }

    public function setDeltaPredictionAndResultMax2017(?float $deltaPredictionAndResultMax2017): void
    {
        $this->deltaPredictionAndResultMax2017 = $deltaPredictionAndResultMax2017;
    }

    public function getDeltaAveragePredictionsMin(): ?float
    {
        return $this->deltaAveragePredictionsMin;
    }

    public function setDeltaAveragePredictionsMin(?float $deltaAveragePredictionsMin): void
    {
        $this->deltaAveragePredictionsMin = $deltaAveragePredictionsMin;
    }

    public function getDeltaAveragePredictionsMax(): ?float
    {
        return $this->deltaAveragePredictionsMax;
    }

    public function setDeltaAveragePredictionsMax(?float $deltaAveragePredictionsMax): void
    {
        $this->deltaAveragePredictionsMax = $deltaAveragePredictionsMax;
    }

    public function getAbstentionsMin2017(): ?float
    {
        return $this->abstentionsMin2017;
    }

    public function setAbstentionsMin2017(?float $abstentionsMin2017): void
    {
        $this->abstentionsMin2017 = $abstentionsMin2017;
    }

    public function getAbstentionsMax2017(): ?float
    {
        return $this->abstentionsMax2017;
    }

    public function setAbstentionsMax2017(?float $abstentionsMax2017): void
    {
        $this->abstentionsMax2017 = $abstentionsMax2017;
    }

    public function getMisregistrationsPriorityMin(): ?int
    {
        return $this->misregistrationsPriorityMin;
    }

    public function setMisregistrationsPriorityMin(?int $misregistrationsPriorityMin): void
    {
        $this->misregistrationsPriorityMin = $misregistrationsPriorityMin;
    }

    public function getMisregistrationsPriorityMax(): ?int
    {
        return $this->misregistrationsPriorityMax;
    }

    public function setMisregistrationsPriorityMax(?int $misregistrationsPriorityMax): void
    {
        $this->misregistrationsPriorityMax = $misregistrationsPriorityMax;
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
}
