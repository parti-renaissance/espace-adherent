<?php

namespace App\Entity\Phoning;

use ApiPlatform\Doctrine\Orm\Filter\DateFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\HttpOperation;
use ApiPlatform\Metadata\Put;
use App\Api\Filter\AdherentIdentityFilter;
use App\Controller\Api\Phoning\CampaignHistoryReplyController;
use App\Entity\Adherent;
use App\Entity\EntityIdentityTrait;
use App\Entity\Jecoute\DataSurvey;
use App\Entity\Jecoute\DataSurveyAwareInterface;
use App\Entity\Jecoute\DataSurveyAwareTrait;
use App\Phoning\CampaignHistoryEngagementEnum;
use App\Phoning\CampaignHistoryStatusEnum;
use App\Phoning\CampaignHistoryTypeEnum;
use App\Repository\Phoning\CampaignHistoryRepository;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiFilter(filterClass: SearchFilter::class, properties: ['campaign.uuid' => 'exact', 'campaign.title' => 'partial', 'status' => 'exact'])]
#[ApiFilter(filterClass: AdherentIdentityFilter::class, properties: ['adherent', 'caller'])]
#[ApiFilter(filterClass: DateFilter::class, properties: ['beginAt'])]
#[ApiResource(
    shortName: 'PhoningCampaignHistory',
    operations: [
        new Put(
            uriTemplate: '/v3/phoning_campaign_histories/{uuid}',
            requirements: ['uuid' => '%pattern_uuid%'],
            security: 'is_granted(\'IS_CAMPAIGN_HISTORY_CALLER\', object)'
        ),
        new HttpOperation(
            method: 'POST',
            uriTemplate: '/v3/phoning_campaign_histories/{uuid}/reply',
            deserialize: false,
            requirements: ['uuid' => '%pattern_uuid%'],
            controller: CampaignHistoryReplyController::class,
            normalizationContext: ['groups' => ['data_survey_read']]
        ),
        new GetCollection(
            uriTemplate: '/v3/phoning_campaign_histories',
            normalizationContext: ['groups' => ['phoning_campaign_history_read_list']],
            security: "is_granted('REQUEST_SCOPE_GRANTED', 'phoning_campaign')"
        ),
    ],
    normalizationContext: ['iri' => true, 'groups' => ['phoning_campaign_history_read']],
    denormalizationContext: ['groups' => ['phoning_campaign_history_write']],
    order: ['beginAt' => 'DESC']
)]
#[ORM\Entity(repositoryClass: CampaignHistoryRepository::class)]
#[ORM\Table(name: 'phoning_campaign_history')]
class CampaignHistory implements DataSurveyAwareInterface
{
    use EntityIdentityTrait;
    use DataSurveyAwareTrait;

    /**
     * @var Adherent|null
     */
    #[Groups(['phoning_campaign_history_read_list', 'phoning_campaign_replies_list'])]
    #[ORM\JoinColumn(onDelete: 'SET NULL')]
    #[ORM\ManyToOne(targetEntity: Adherent::class)]
    private $caller;

    /**
     * @var Adherent|null
     */
    #[Groups(['phoning_campaign_call_read', 'phoning_campaign_history_read_list', 'phoning_campaign_replies_list'])]
    #[ORM\JoinColumn(onDelete: 'SET NULL')]
    #[ORM\ManyToOne(targetEntity: Adherent::class)]
    private $adherent;

    /**
     * @var Campaign
     */
    #[Groups(['phoning_campaign_history_read_list', 'phoning_campaign_replies_list'])]
    #[ORM\JoinColumn(nullable: false)]
    #[ORM\ManyToOne(targetEntity: Campaign::class, inversedBy: 'campaignHistories')]
    private $campaign;

    /**
     * @var string
     */
    #[Assert\Choice(callback: [CampaignHistoryTypeEnum::class, 'toArray'], message: 'phoning.campaign_history.type.invalid_choice')]
    #[Groups(['phoning_campaign_history_write', 'phoning_campaign_history_read_list'])]
    #[ORM\Column(length: 10, nullable: true)]
    private $type;

    /**
     * @var string|null
     */
    #[Assert\Choice(choices: CampaignHistoryStatusEnum::AFTER_CALL_STATUS, message: 'phoning.campaign_history.status.invalid_choice')]
    #[Assert\NotNull]
    #[Groups(['phoning_campaign_history_write', 'phoning_campaign_history_read', 'phoning_campaign_history_read_list'])]
    #[ORM\Column(length: 25)]
    private $status;

    /**
     * @var bool|null
     */
    #[Groups(['phoning_campaign_history_write', 'phoning_campaign_history_read_list'])]
    #[ORM\Column(type: 'boolean', nullable: true)]
    protected $postalCodeChecked;

    /**
     * @var string|null
     */
    #[Assert\Length(max: 255)]
    #[Groups(['phoning_campaign_history_write', 'phoning_campaign_history_read_list'])]
    #[ORM\Column(nullable: true)]
    private $profession;

    /**
     * @var bool|null
     */
    #[Groups(['phoning_campaign_history_write', 'phoning_campaign_history_read_list'])]
    #[ORM\Column(type: 'boolean', nullable: true)]
    private $needEmailRenewal;

    /**
     * @var bool|null
     */
    #[Groups(['phoning_campaign_history_write', 'phoning_campaign_history_read_list'])]
    #[ORM\Column(type: 'boolean', nullable: true)]
    private $needSmsRenewal;

    /**
     * @var string|null
     */
    #[Assert\Choice(callback: [CampaignHistoryEngagementEnum::class, 'toArray'], message: 'phoning.campaign_history.engagement.invalid_choice')]
    #[Groups(['phoning_campaign_history_write', 'phoning_campaign_history_read_list'])]
    #[ORM\Column(length: 20, nullable: true)]
    private $engagement;

    /**
     * @var int|null
     */
    #[Assert\Range(min: '1', max: '5')]
    #[Groups(['phoning_campaign_history_write', 'phoning_campaign_history_read_list'])]
    #[ORM\Column(type: 'smallint', nullable: true, options: ['unsigned' => true])]
    private $note;

    /**
     * @var \DateTime
     */
    #[Assert\NotBlank]
    #[Groups(['phoning_campaign_history_read_list', 'phoning_campaign_replies_list', 'survey_replies_list'])]
    #[ORM\Column(type: 'datetime')]
    private $beginAt;

    /**
     * @var \DateTime|null
     */
    #[Assert\Expression('value === null or value > this.getBeginAt()', message: 'phoning.campaign_history.finish_at.invalid')]
    #[Groups(['phoning_campaign_history_read_list', 'phoning_campaign_replies_list', 'survey_replies_list'])]
    #[ORM\Column(type: 'datetime', nullable: true)]
    private $finishAt;

    #[Assert\Valid]
    #[Groups(['phoning_campaign_history_read_list'])]
    #[ORM\JoinColumn(onDelete: 'SET NULL')]
    #[ORM\OneToOne(inversedBy: 'phoningCampaignHistory', targetEntity: DataSurvey::class, cascade: ['persist'], orphanRemoval: true)]
    private ?DataSurvey $dataSurvey = null;

    public function __construct(Campaign $campaign, ?UuidInterface $uuid = null)
    {
        $this->campaign = $campaign;
        $this->uuid = $uuid ?? Uuid::uuid4();
    }

    public static function createForCampaign(
        Campaign $campaign,
        Adherent $caller,
        ?Adherent $adherent,
        ?UuidInterface $uuid = null,
    ): self {
        $history = new self($campaign, $uuid);
        $history->caller = $caller;
        $history->adherent = $adherent;
        $history->status = CampaignHistoryStatusEnum::SEND;
        $history->beginAt = new \DateTime();

        return $history;
    }

    public function getCaller(): ?Adherent
    {
        return $this->caller;
    }

    public function getAdherent(): ?Adherent
    {
        return $this->adherent;
    }

    public function setAdherent(?Adherent $adherent): void
    {
        $this->adherent = $adherent;
    }

    public function getCampaign(): Campaign
    {
        return $this->campaign;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): void
    {
        $this->type = $type;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(?string $status): void
    {
        $this->status = $status;
    }

    public function isPostalCodeChecked(): ?bool
    {
        return $this->postalCodeChecked;
    }

    public function setPostalCodeChecked(?bool $postalCodeChecked): void
    {
        $this->postalCodeChecked = $postalCodeChecked;
    }

    public function getProfession(): ?string
    {
        return $this->profession;
    }

    public function setProfession(?string $profession): void
    {
        $this->profession = $profession;
    }

    public function getNeedEmailRenewal(): ?bool
    {
        return $this->needEmailRenewal;
    }

    public function setNeedEmailRenewal(?bool $needEmailRenewal): void
    {
        $this->needEmailRenewal = $needEmailRenewal;
    }

    public function getNeedSmsRenewal(): ?bool
    {
        return $this->needSmsRenewal;
    }

    public function setNeedSmsRenewal(?bool $needSmsRenewal): void
    {
        $this->needSmsRenewal = $needSmsRenewal;
    }

    public function getEngagement(): ?string
    {
        return $this->engagement;
    }

    public function setEngagement(?string $engagement): void
    {
        $this->engagement = $engagement;
    }

    public function getNote(): ?int
    {
        return $this->note;
    }

    public function setNote(?int $note): void
    {
        $this->note = $note;
    }

    public function getBeginAt(): \DateTimeInterface
    {
        return $this->beginAt;
    }

    public function setBeginAt(\DateTimeInterface $beginAt): void
    {
        $this->beginAt = $beginAt;
    }

    public function getFinishAt(): ?\DateTimeInterface
    {
        return $this->finishAt;
    }

    public function setFinishAt(?\DateTimeInterface $finishAt): void
    {
        $this->finishAt = $finishAt;
    }

    public function isInAfterCallStatus(): bool
    {
        return \in_array($this->status, CampaignHistoryStatusEnum::AFTER_CALL_STATUS);
    }

    public function isToUnjoinStatus(): bool
    {
        return CampaignHistoryStatusEnum::TO_UNJOIN === $this->status;
    }

    public function isToUnsubscribeStatus(): bool
    {
        return CampaignHistoryStatusEnum::TO_UNSUBSCRIBE === $this->status;
    }

    public function isToRemindStatus(): bool
    {
        return CampaignHistoryStatusEnum::TO_REMIND === $this->status;
    }

    public function isFailedStatus(): bool
    {
        return CampaignHistoryStatusEnum::FAILED === $this->status;
    }

    public function isNotRespondStatus(): bool
    {
        return CampaignHistoryStatusEnum::NOT_RESPOND === $this->status;
    }
}
