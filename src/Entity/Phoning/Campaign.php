<?php

namespace App\Entity\Phoning;

use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Annotation\ApiSubresource;
use App\Entity\Adherent;
use App\Entity\Audience\AudienceSnapshot;
use App\Entity\EntityAdministratorTrait;
use App\Entity\EntityIdentityTrait;
use App\Entity\EntityTimestampableTrait;
use App\Entity\Jecoute\Survey;
use App\Entity\Team\Team;
use App\Phoning\CampaignHistoryStatusEnum;
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
 * @ApiResource(
 *     attributes={
 *         "normalization_context": {
 *             "iri": true,
 *             "groups": {"phoning_campaign_read"},
 *         },
 *     },
 *     itemOperations={
 *         "get_with_scores": {
 *             "method": "GET",
 *             "path": "/v3/phoning_campaigns/{id}/scores",
 *             "requirements": {"id": "%pattern_uuid%"},
 *             "access_control": "is_granted('CAN_MANAGE_PHONING_CAMPAIGN', object)",
 *             "normalization_context": {
 *                 "groups": {"phoning_campaign_read_with_score"},
 *             },
 *         },
 *     },
 *     collectionOperations={
 *         "get_my_phoning_campaigns_scores": {
 *             "method": "GET",
 *             "path": "/v3/phoning_campaigns/scores",
 *             "controller": "App\Controller\Api\Phoning\CampaignsScoresController",
 *             "normalization_context": {
 *                 "iri": true,
 *                 "groups": {"phoning_campaign_read_with_score"},
 *             },
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
 */
class Campaign
{
    use EntityIdentityTrait;
    use EntityTimestampableTrait;
    use EntityAdministratorTrait;

    /**
     * @var string|null
     *
     * @ORM\Column
     *
     * @Assert\NotBlank
     * @Assert\Length(max=255)
     *
     * @Groups({"phoning_campaign_read", "phoning_campaign_read_with_score"})
     */
    private $title;

    /**
     * @var string|null
     *
     * @ORM\Column(type="text", nullable=true)
     *
     * @Groups({"phoning_campaign_read", "phoning_campaign_read_with_score"})
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
     * @Groups({"phoning_campaign_read", "phoning_campaign_read_with_score"})
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
     * @Groups({"phoning_campaign_read", "phoning_campaign_read_with_score"})
     */
    private $finishAt;

    /**
     * @var Team|null
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Team\Team")
     * @ORM\JoinColumn(onDelete="CASCADE")
     *
     * @Assert\NotBlank(groups={"regular_campaign"})
     */
    private $team;

    /**
     * @var AudienceSnapshot|null
     *
     * @ORM\OneToOne(targetEntity="App\Entity\Audience\AudienceSnapshot", cascade={"persist"})
     * @ORM\JoinColumn
     *
     * @Assert\NotBlank(groups={"regular_campaign"})
     */
    private $audience;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Jecoute\Survey")
     * @ORM\JoinColumn(nullable=false)
     *
     * @Assert\NotBlank
     *
     * @ApiSubresource
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

    public function __construct(
        UuidInterface $uuid = null,
        string $title = null,
        string $brief = null,
        Team $team = null,
        AudienceSnapshot $audience = null,
        Survey $survey = null,
        int $goal = null,
        \DateTimeInterface $finishAt = null
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

    public function getFinishAt(): ?\DateTime
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
}
