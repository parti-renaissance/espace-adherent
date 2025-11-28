<?php

declare(strict_types=1);

namespace App\Entity\Phoning;

use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Api\Filter\ScopeVisibilityFilter;
use App\Controller\Api\Phoning\CampaignsScoresController;
use App\Controller\Api\Phoning\GetPhoningCampaignCallersStatsController;
use App\Controller\Api\Phoning\GetPhoningCampaignsKpiController;
use App\Entity\Adherent;
use App\Entity\Audience\AudienceSnapshot;
use App\Entity\EntityAdherentBlameableInterface;
use App\Entity\EntityAdherentBlameableTrait;
use App\Entity\EntityAdministratorBlameableInterface;
use App\Entity\EntityAdministratorBlameableTrait;
use App\Entity\EntityIdentityTrait;
use App\Entity\EntityScopeVisibilityTrait;
use App\Entity\EntityScopeVisibilityWithZoneInterface;
use App\Entity\EntityTimestampableTrait;
use App\Entity\Geo\Zone;
use App\Entity\IndexableEntityInterface;
use App\Entity\Jecoute\Survey;
use App\Entity\Team\Team;
use App\EntityListener\AlgoliaIndexListener;
use App\Phoning\CampaignHistoryStatusEnum;
use App\Repository\Phoning\CampaignRepository;
use App\Validator\Scope\ScopeVisibility;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiFilter(filterClass: ScopeVisibilityFilter::class)]
#[ApiFilter(filterClass: SearchFilter::class, properties: ['visibility' => 'exact'])]
#[ApiResource(
    shortName: 'PhoningCampaign',
    operations: [
        new Get(
            uriTemplate: '/v3/phoning_campaigns/{uuid}',
            requirements: ['uuid' => '%pattern_uuid%'],
            security: "is_granted('REQUEST_SCOPE_GRANTED', 'phoning_campaign')"
        ),
        new Put(
            uriTemplate: '/v3/phoning_campaigns/{uuid}',
            requirements: ['uuid' => '%pattern_uuid%'],
            security: "is_granted('REQUEST_SCOPE_GRANTED', 'phoning_campaign') and is_granted('SCOPE_CAN_MANAGE', object)"
        ),
        new Get(
            uriTemplate: '/v3/phoning_campaigns/{uuid}/scores',
            requirements: ['uuid' => '%pattern_uuid%'],
            normalizationContext: ['groups' => ['phoning_campaign_read_with_score']],
            security: "is_granted('CAN_MANAGE_PHONING_CAMPAIGN', object)"
        ),
        new Get(
            uriTemplate: '/v3/phoning_campaigns/{uuid}/callers',
            requirements: ['uuid' => '%pattern_uuid%'],
            controller: GetPhoningCampaignCallersStatsController::class,
            security: "is_granted('REQUEST_SCOPE_GRANTED', 'phoning_campaign')"
        ),
        new GetCollection(
            uriTemplate: '/v3/phoning_campaigns',
            normalizationContext: ['groups' => ['phoning_campaign_list']],
            security: "is_granted('REQUEST_SCOPE_GRANTED', 'phoning_campaign')"
        ),
        new Post(
            uriTemplate: '/v3/phoning_campaigns',
            security: "is_granted('REQUEST_SCOPE_GRANTED', 'phoning_campaign')"
        ),
        new GetCollection(
            uriTemplate: '/v3/phoning_campaigns/scores',
            controller: CampaignsScoresController::class,
            normalizationContext: ['iri' => true, 'groups' => ['phoning_campaign_read_with_score']]
        ),
        new GetCollection(
            uriTemplate: '/v3/phoning_campaigns/kpi',
            controller: GetPhoningCampaignsKpiController::class,
            security: "is_granted('REQUEST_SCOPE_GRANTED', 'phoning_campaign')"
        ),
    ],
    normalizationContext: ['groups' => ['phoning_campaign_read']],
    denormalizationContext: ['groups' => ['phoning_campaign_write']],
    order: ['createdAt' => 'DESC'],
    paginationClientItemsPerPage: true
)]
#[ORM\Entity(repositoryClass: CampaignRepository::class)]
#[ORM\EntityListeners([AlgoliaIndexListener::class])]
#[ORM\Table(name: 'phoning_campaign')]
#[ScopeVisibility]
class Campaign implements EntityAdherentBlameableInterface, EntityAdministratorBlameableInterface, IndexableEntityInterface, EntityScopeVisibilityWithZoneInterface
{
    use EntityIdentityTrait;
    use EntityTimestampableTrait;
    use EntityAdministratorBlameableTrait;
    use EntityAdherentBlameableTrait;
    use EntityScopeVisibilityTrait;

    /**
     * @var string|null
     */
    #[Assert\Length(max: 255)]
    #[Assert\NotBlank]
    #[Groups(['phoning_campaign_read', 'phoning_campaign_read_with_score', 'phoning_campaign_list', 'phoning_campaign_write', 'phoning_campaign_history_read_list', 'phoning_campaign_replies_list'])]
    #[ORM\Column]
    private $title;

    /**
     * @var string|null
     */
    #[Groups(['phoning_campaign_read', 'phoning_campaign_read_with_score', 'phoning_campaign_write'])]
    #[ORM\Column(type: 'text', nullable: true)]
    private $brief;

    /**
     * @var int|null
     */
    #[Assert\GreaterThan(value: '0')]
    #[Assert\NotBlank]
    #[Groups(['phoning_campaign_read', 'phoning_campaign_read_with_score', 'phoning_campaign_list', 'phoning_campaign_write'])]
    #[ORM\Column(type: 'integer')]
    private $goal;

    /**
     * @var \DateTime|null
     */
    #[Assert\NotBlank(groups: ['regular_campaign'])]
    #[Groups(['phoning_campaign_read', 'phoning_campaign_read_with_score', 'phoning_campaign_list', 'phoning_campaign_write'])]
    #[ORM\Column(type: 'datetime', nullable: true)]
    private $finishAt;

    /**
     * @var Team|null
     */
    #[Assert\NotBlank(groups: ['regular_campaign'])]
    #[Groups(['phoning_campaign_read', 'phoning_campaign_list', 'phoning_campaign_write'])]
    #[ORM\JoinColumn(onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: Team::class)]
    private $team;

    /**
     * @var AudienceSnapshot|null
     */
    #[Assert\NotBlank(groups: ['regular_campaign'])]
    #[Groups(['audience_read', 'phoning_campaign_read', 'phoning_campaign_write'])]
    #[ORM\JoinColumn]
    #[ORM\OneToOne(targetEntity: AudienceSnapshot::class, cascade: ['all'], orphanRemoval: true)]
    private $audience;

    #[Assert\NotBlank]
    #[Groups(['phoning_campaign_read', 'phoning_campaign_write'])]
    #[ORM\JoinColumn(nullable: false)]
    #[ORM\ManyToOne(targetEntity: Survey::class)]
    private $survey;

    /**
     * @var Collection|CampaignHistory[]
     */
    #[ORM\OneToMany(mappedBy: 'campaign', targetEntity: CampaignHistory::class, fetch: 'EXTRA_LAZY')]
    private $campaignHistories;

    #[Groups(['phoning_campaign_read', 'phoning_campaign_read_with_score'])]
    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private bool $permanent = false;

    #[Groups(['phoning_campaign_read', 'phoning_campaign_list'])]
    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private int $participantsCount = 0;

    public function __construct(
        ?UuidInterface $uuid = null,
        ?string $title = null,
        ?string $brief = null,
        ?Team $team = null,
        ?AudienceSnapshot $audience = null,
        ?Survey $survey = null,
        ?int $goal = null,
        ?\DateTime $finishAt = null,
        ?Zone $zone = null,
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

    public function setFinishAt(?\DateTimeInterface $finishAt): void
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

    #[Groups(['phoning_campaign_read', 'phoning_campaign_list'])]
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

    public function isIndexable(): bool
    {
        return true;
    }
}
