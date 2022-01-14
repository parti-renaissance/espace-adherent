<?php

namespace App\Entity\Phoning;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Annotation\ApiSubresource;
use App\Api\Filter\ScopeVisibilityFilter;
use App\Entity\Adherent;
use App\Entity\Audience\AudienceSnapshot;
use App\Entity\EntityAdherentBlameableInterface;
use App\Entity\EntityAdherentBlameableTrait;
use App\Entity\EntityAdministratorBlameableInterface;
use App\Entity\EntityAdministratorBlameableTrait;
use App\Entity\EntityIdentityTrait;
use App\Entity\EntityScopeVisibilityInterface;
use App\Entity\EntityScopeVisibilityTrait;
use App\Entity\EntityTimestampableTrait;
use App\Entity\Geo\Zone;
use App\Entity\IndexableEntityInterface;
use App\Entity\Jecoute\Survey;
use App\Entity\Team\Team;
use App\Phoning\CampaignHistoryStatusEnum;
use App\Validator\Scope\ScopeVisibility;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\Phoning\CampaignRepository")
 * @ORM\Table(name="phoning_campaign")
 *
 * @ORM\EntityListeners({"App\EntityListener\AlgoliaIndexListener"})
 *
 * @ApiResource(
 *     shortName="PhoningCampaign",
 *     attributes={
 *         "normalization_context": {
 *             "groups": {"phoning_campaign_read"},
 *         },
 *         "denormalization_context": {
 *             "groups": {"phoning_campaign_write"}
 *         },
 *         "order": {"createdAt": "DESC"},
 *         "pagination_client_items_per_page": true
 *     },
 *     itemOperations={
 *         "get": {
 *             "path": "/v3/phoning_campaigns/{id}",
 *             "requirements": {"id": "%pattern_uuid%"},
 *             "access_control": "is_granted('IS_FEATURE_GRANTED', 'phoning_campaign')"
 *         },
 *         "put": {
 *             "path": "/v3/phoning_campaigns/{id}",
 *             "requirements": {"id": "%pattern_uuid%"},
 *             "access_control": "is_granted('IS_FEATURE_GRANTED', 'phoning_campaign') and is_granted('SCOPE_CAN_MANAGE', object)"
 *         },
 *         "get_with_scores_public": {
 *             "method": "GET",
 *             "path": "/v3/phoning_campaigns/{id}/scores",
 *             "requirements": {"id": "%pattern_uuid%"},
 *             "access_control": "is_granted('CAN_MANAGE_PHONING_CAMPAIGN', object)",
 *             "normalization_context": {
 *                 "groups": {"phoning_campaign_read_with_score"},
 *             },
 *         },
 *         "get_callers_with_scores_private": {
 *             "method": "GET",
 *             "path": "/v3/phoning_campaigns/{uuid}/callers",
 *             "requirements": {"uuid": "%pattern_uuid%"},
 *             "access_control": "is_granted('IS_FEATURE_GRANTED', 'phoning_campaign')",
 *             "controller": "App\Controller\Api\Phoning\GetPhoningCampaignCallersStatsController",
 *             "defaults": {"_api_receive": false},
 *         }
 *     },
 *     collectionOperations={
 *         "get": {
 *             "path": "/v3/phoning_campaigns",
 *             "normalization_context": {
 *                 "groups": {"phoning_campaign_list"},
 *             },
 *             "access_control": "is_granted('IS_FEATURE_GRANTED', 'phoning_campaign')"
 *         },
 *         "post": {
 *             "path": "/v3/phoning_campaigns",
 *             "access_control": "is_granted('IS_FEATURE_GRANTED', 'phoning_campaign')"
 *         },
 *         "get_my_phoning_campaigns_scores": {
 *             "method": "GET",
 *             "path": "/v3/phoning_campaigns/scores",
 *             "controller": "App\Controller\Api\Phoning\CampaignsScoresController",
 *             "normalization_context": {
 *                 "iri": true,
 *                 "groups": {"phoning_campaign_read_with_score"},
 *             },
 *         },
 *         "get_phoning_campaigns_kpi": {
 *             "method": "GET",
 *             "path": "/v3/phoning_campaigns/kpi",
 *             "controller": "App\Controller\Api\Phoning\GetPhoningCampaignsKpiController",
 *             "access_control": "is_granted('IS_FEATURE_GRANTED', 'phoning_campaign')",
 *         },
 *     },
 *     subresourceOperations={
 *         "survey_get_subresource": {
 *             "method": "GET",
 *             "path": "/v3/phoning_campaigns/{id}/survey",
 *             "access_control": "object.isPermanent() or is_granted('ROLE_PHONING_CAMPAIGN_MEMBER')",
 *             "requirements": {"id": "%pattern_uuid%"},
 *         },
 *     },
 * )
 *
 * @ApiFilter(ScopeVisibilityFilter::class)
 *
 * @ScopeVisibility
 */
class Campaign implements EntityAdherentBlameableInterface, EntityAdministratorBlameableInterface, IndexableEntityInterface, EntityScopeVisibilityInterface
{
    use EntityIdentityTrait;
    use EntityTimestampableTrait;
    use EntityAdministratorBlameableTrait;
    use EntityAdherentBlameableTrait;
    use EntityScopeVisibilityTrait;

    /**
     * @var string|null
     *
     * @ORM\Column
     *
     * @Assert\NotBlank
     * @Assert\Length(max=255)
     *
     * @Groups({
     *     "phoning_campaign_read",
     *     "phoning_campaign_read_with_score",
     *     "phoning_campaign_list",
     *     "phoning_campaign_write",
     *     "phoning_campaign_history_read_list",
     *     "phoning_campaign_replies_list",
     * })
     */
    private $title;

    /**
     * @var string|null
     *
     * @ORM\Column(type="text", nullable=true)
     *
     * @Groups({"phoning_campaign_read", "phoning_campaign_read_with_score", "phoning_campaign_write"})
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
     * @Groups({"phoning_campaign_read", "phoning_campaign_read_with_score", "phoning_campaign_list", "phoning_campaign_write"})
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
     * @Groups({"phoning_campaign_read", "phoning_campaign_read_with_score", "phoning_campaign_list", "phoning_campaign_write"})
     */
    private $finishAt;

    /**
     * @var Team|null
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Team\Team")
     * @ORM\JoinColumn(onDelete="CASCADE")
     *
     * @Assert\NotBlank(groups={"regular_campaign"})
     *
     * @Groups({"phoning_campaign_read", "phoning_campaign_list", "phoning_campaign_write"})
     */
    private $team;

    /**
     * @var AudienceSnapshot|null
     *
     * @ORM\OneToOne(targetEntity="App\Entity\Audience\AudienceSnapshot", cascade={"all"}, orphanRemoval=true)
     * @ORM\JoinColumn
     *
     * @Assert\NotBlank(groups={"regular_campaign"})
     *
     * @Groups({"audience_read", "phoning_campaign_read", "phoning_campaign_write"})
     */
    private $audience;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Jecoute\Survey")
     * @ORM\JoinColumn(nullable=false)
     *
     * @Assert\NotBlank
     *
     * @ApiSubresource
     *
     * @Groups({"phoning_campaign_read", "phoning_campaign_write"})
     */
    private $survey;

    /**
     * @var Collection|CampaignHistory[]
     *
     * @ORM\OneToMany(targetEntity="App\Entity\Phoning\CampaignHistory", mappedBy="campaign", fetch="EXTRA_LAZY")
     */
    private $campaignHistories;

    /**
     * @ORM\Column(type="boolean", options={"default": false})
     *
     * @Groups({"phoning_campaign_read", "phoning_campaign_read_with_score"})
     */
    private bool $permanent = false;

    /** @ORM\Column(type="integer", options={"default": 0}) */
    private int $participantsCount = 0;

    public function __construct(
        UuidInterface $uuid = null,
        string $title = null,
        string $brief = null,
        Team $team = null,
        AudienceSnapshot $audience = null,
        Survey $survey = null,
        int $goal = null,
        \DateTimeInterface $finishAt = null,
        Zone $zone = null
    ) {
        $this->uuid = $uuid ?? Uuid::uuid4();
        $this->title = $title;
        $this->brief = $brief;
        $this->team = $team;
        $this->audience = $audience;
        $this->survey = $survey;
        $this->goal = $goal;
        $this->finishAt = $finishAt;
        $this->campaignHistories = new ArrayCollection();

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

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    public function getBrief(): ?string
    {
        return $this->brief;
    }

    public function setBrief(string $brief): void
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

    public function getFinishAt(): ?\DateTimeInterface
    {
        return $this->finishAt;
    }

    public function setFinishAt(?\DateTime $finishAt): void
    {
        $this->finishAt = $finishAt;
    }

    public function getTeam(): ?Team
    {
        return $this->team;
    }

    public function setTeam(?Team $team): void
    {
        $this->team = $team;
    }

    public function getAudience(): ?AudienceSnapshot
    {
        return $this->audience;
    }

    public function setAudience(AudienceSnapshot $audience): void
    {
        $this->audience = $audience;
    }

    public function getSurvey(): ?Survey
    {
        return $this->survey;
    }

    public function setSurvey(Survey $survey): void
    {
        $this->survey = $survey;
    }

    /**
     * @return CampaignHistory[]|Collection
     */
    public function getCampaignHistories(): Collection
    {
        return $this->campaignHistories;
    }

    public function getCampaignHistoriesCount(): int
    {
        return $this->campaignHistories->count();
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

    /**
     * @return CampaignHistory[]|Collection
     */
    public function getCampaignHistoriesForAdherent(Adherent $adherent): Collection
    {
        $criteria = Criteria::create()
            ->andWhere(Criteria::expr()->neq('status', CampaignHistoryStatusEnum::SEND))
            ->andWhere(Criteria::expr()->eq('caller', $adherent))
        ;

        return $this->campaignHistories->matching($criteria);
    }

    /**
     * @return CampaignHistory[]|Collection
     */
    public function getCampaignHistoriesWithDataSurveyForAdherent(Adherent $adherent): Collection
    {
        $criteria = Criteria::create()
            ->andWhere(Criteria::expr()->neq('status', CampaignHistoryStatusEnum::SEND))
            ->andWhere(Criteria::expr()->eq('caller', $adherent))
            ->andWhere(Criteria::expr()->neq('dataSurvey', null))
        ;

        return $this->campaignHistories->matching($criteria);
    }

    public function getCampaignHistoriesToUnsubscribe(): Collection
    {
        return $this->campaignHistories->filter(function (CampaignHistory $campaignHistory) {
            return $campaignHistory->isToUnsubscribeStatus();
        });
    }

    public function getCampaignHistoriesToUnjoin(): Collection
    {
        return $this->campaignHistories->filter(function (CampaignHistory $campaignHistory) {
            return $campaignHistory->isToUnjoinStatus();
        });
    }

    public function getCampaignHistoriesToRemind(): Collection
    {
        return $this->campaignHistories->filter(function (CampaignHistory $campaignHistory) {
            return $campaignHistory->isToRemindStatus();
        });
    }

    public function getCampaignHistoriesNotRespond(): Collection
    {
        return $this->campaignHistories->filter(function (CampaignHistory $campaignHistory) {
            return $campaignHistory->isNotRespondStatus();
        });
    }

    public function getCampaignHistoriesFailed(): Collection
    {
        return $this->campaignHistories->filter(function (CampaignHistory $campaignHistory) {
            return $campaignHistory->isFailedStatus();
        });
    }

    public function isFinished(): bool
    {
        return null !== $this->finishAt && $this->finishAt <= new \DateTime();
    }

    public function getGoalOverall(): int
    {
        return (int) $this->goal * ($this->getTeam() ? $this->getTeam()->getMembersCount() : 1);
    }

    public function isPermanent(): bool
    {
        return $this->permanent;
    }

    public function setPermanent(bool $value): void
    {
        $this->permanent = $value;
    }

    /**
     * @Groups({"phoning_campaign_read", "phoning_campaign_list"})
     */
    public function getCreator(): string
    {
        return null !== $this->createdByAdherent ? $this->createdByAdherent->getPartialName() : 'Admin';
    }

    public function getParticipantsCount(): int
    {
        return $this->participantsCount;
    }

    public function setParticipantsCount(int $participantsCount): void
    {
        $this->participantsCount = $participantsCount;
    }

    public function getIndexOptions(): array
    {
        return [];
    }

    public function isIndexable(): bool
    {
        return true;
    }
}
