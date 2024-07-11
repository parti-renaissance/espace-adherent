<?php

namespace App\Entity\Phoning;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Doctrine\Orm\Filter\DateFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use App\Api\Filter\AdherentIdentityFilter;
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
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Table(name: 'phoning_campaign_history')]
#[ORM\Entity(repositoryClass: CampaignHistoryRepository::class)]
#[ApiResource(shortName: 'PhoningCampaignHistory', attributes: ['normalization_context' => ['iri' => true, 'groups' => ['phoning_campaign_history_read']], 'denormalization_context' => ['groups' => ['phoning_campaign_history_write']], 'order' => ['beginAt' => 'DESC']], collectionOperations: ['get' => ['path' => '/v3/phoning_campaign_histories', 'security' => "is_granted('IS_FEATURE_GRANTED', 'phoning_campaign')", 'normalization_context' => ['groups' => ['phoning_campaign_history_read_list']]]], itemOperations: ['put' => ['path' => '/v3/phoning_campaign_histories/{uuid}', 'requirements' => ['uuid' => '%pattern_uuid%'], 'security' => "is_granted('IS_CAMPAIGN_HISTORY_CALLER', object)"], 'post_reply' => ['method' => 'POST', 'path' => '/v3/phoning_campaign_histories/{uuid}/reply', 'requirements' => ['uuid' => '%pattern_uuid%'], 'controller' => 'App\Controller\Api\Phoning\CampaignHistoryReplyController', 'defaults' => ['_api_receive' => false], 'normalization_context' => ['groups' => ['data_survey_read']]]])]
#[ApiFilter(SearchFilter::class, properties: ['campaign.uuid' => 'exact', 'campaign.title' => 'partial', 'status' => 'exact'])]
#[ApiFilter(AdherentIdentityFilter::class, properties: ['adherent', 'caller'])]
#[ApiFilter(DateFilter::class, properties: ['beginAt'])]
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
    #[Groups(['phoning_campaign_history_write', 'phoning_campaign_history_read_list'])]
    #[ORM\Column(length: 10, nullable: true)]
    #[Assert\Choice(callback: [CampaignHistoryTypeEnum::class, 'toArray'], message: 'phoning.campaign_history.type.invalid_choice')]
    private $type;

    /**
     * @var string|null
     */
    #[Groups(['phoning_campaign_history_write', 'phoning_campaign_history_read', 'phoning_campaign_history_read_list'])]
    #[ORM\Column(length: 25)]
    #[Assert\NotNull]
    #[Assert\Choice(choices: CampaignHistoryStatusEnum::AFTER_CALL_STATUS, message: 'phoning.campaign_history.status.invalid_choice')]
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
    #[Groups(['phoning_campaign_history_write', 'phoning_campaign_history_read_list'])]
    #[ORM\Column(nullable: true)]
    #[Assert\Length(max: 255)]
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
    #[Groups(['phoning_campaign_history_write', 'phoning_campaign_history_read_list'])]
    #[ORM\Column(length: 20, nullable: true)]
    #[Assert\Choice(callback: [CampaignHistoryEngagementEnum::class, 'toArray'], message: 'phoning.campaign_history.engagement.invalid_choice')]
    private $engagement;

    /**
     * @var int|null
     */
    #[Groups(['phoning_campaign_history_write', 'phoning_campaign_history_read_list'])]
    #[ORM\Column(type: 'smallint', nullable: true, options: ['unsigned' => true])]
    #[Assert\Range(min: '1', max: '5')]
    private $note;

    /**
     * @var \DateTime
     */
    #[Groups(['phoning_campaign_history_read_list', 'phoning_campaign_replies_list', 'survey_replies_list'])]
    #[ORM\Column(type: 'datetime')]
    #[Assert\NotBlank]
    private $beginAt;

    /**
     * @var \DateTime|null
     */
    #[Groups(['phoning_campaign_history_read_list', 'phoning_campaign_replies_list', 'survey_replies_list'])]
    #[ORM\Column(type: 'datetime', nullable: true)]
    #[Assert\Expression('value === null or value > this.getBeginAt()', message: 'phoning.campaign_history.finish_at.invalid')]
    private $finishAt;

    #[Groups(['phoning_campaign_history_read_list'])]
    #[ORM\JoinColumn(onDelete: 'SET NULL')]
    #[ORM\OneToOne(inversedBy: 'phoningCampaignHistory', targetEntity: DataSurvey::class, cascade: ['persist'], orphanRemoval: true)]
    #[Assert\Valid]
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
        ?UuidInterface $uuid = null
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
