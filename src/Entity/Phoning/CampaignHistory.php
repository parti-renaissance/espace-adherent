<?php

namespace App\Entity\Phoning;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Entity\Adherent;
use App\Entity\EntityIdentityTrait;
use App\Entity\Jecoute\DataSurveyAwareInterface;
use App\Entity\Jecoute\DataSurveyAwareTrait;
use App\Phoning\CampaignHistoryStatusEnum;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="phoning_campaign_history")
 *
 * @ApiResource(
 *     attributes={
 *         "normalization_context": {
 *             "iri": true,
 *             "groups": {"phoning_campaign_history_read"},
 *         },
 *         "denormalization_context": {"groups": {"phoning_campaign_history_write"}},
 *     },
 *     itemOperations={
 *         "put": {
 *             "path": "/v3/phoning_campaign_histories/{id}",
 *             "requirements": {"id": "%pattern_uuid%"},
 *             "access_control": "is_granted('ROLE_PHONING_CAMPAIGN_MEMBER') and is_granted('IS_CAMPAIGN_HISTORY_CALLER', object)",
 *         },
 *     }
 * )
 */
class CampaignHistory implements DataSurveyAwareInterface
{
    use EntityIdentityTrait;
    use DataSurveyAwareTrait;

    /**
     * @var Adherent|null
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Adherent")
     * @ORM\JoinColumn(onDelete="SET NULL")
     */
    private $caller;

    /**
     * @var Adherent
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Adherent")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     *
     * @Groups({"phoning_campaign_call_read"})
     */
    private $adherent;

    /**
     * @var Campaign
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Phoning\Campaign", inversedBy="campaignHistories")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private $campaign;

    /**
     * @var string
     *
     * @ORM\Column(length=10, nullable=true)
     *
     * @Assert\Choice(
     *     callback={"App\Phoning\CampaignHistoryTypeEnum", "toArray"},
     *     message="phoning.campaign_history.type.invalid_choice",
     *     strict=true
     * )
     */
    private $type;

    /**
     * @var string
     *
     * @ORM\Column(length=25)
     *
     * @Assert\NotNull
     * @Assert\Choice(
     *     choices=App\Phoning\CampaignHistoryStatusEnum::AFTER_CALL_STATUS,
     *     message="phoning.campaign_history.status.invalid_choice",
     *     strict=true
     * )
     *
     * @Groups({"phoning_campaign_history_write", "phoning_campaign_history_read"})
     */
    private $status;

    /**
     * @var bool|null
     *
     * @ORM\Column(type="boolean", nullable=true)
     *
     * @Groups({"phoning_campaign_history_write"})
     */
    protected $postalCodeChecked;

    /**
     * @var bool|null
     *
     * @ORM\Column(type="boolean", nullable=true)
     *
     * @Groups({"phoning_campaign_history_write"})
     */
    protected $callMore;

    /**
     * @var bool|null
     *
     * @ORM\Column(type="boolean", nullable=true)
     *
     * @Groups({"phoning_campaign_history_write"})
     */
    protected $needRenewal;

    /**
     * @var bool|null
     *
     * @ORM\Column(type="boolean", nullable=true)
     *
     * @Groups({"phoning_campaign_history_write"})
     */
    protected $becomeCaller;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime")
     *
     * @Assert\NotBlank
     * @Assert\DateTime
     */
    private $beginAt;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(type="datetime", nullable=true)
     *
     * @Assert\DateTime
     * @Assert\Expression(
     *     "value === null or value > this.getBeginAt()",
     *     message="phoning.campaign_history.finish_at.invalid"
     * )
     */
    private $finishAt;

    public function __construct(Campaign $campaign, UuidInterface $uuid = null)
    {
        $this->campaign = $campaign;
        $this->uuid = $uuid ?? Uuid::uuid4();
    }

    public static function createForCampaign(
        Campaign $campaign,
        Adherent $caller,
        Adherent $adherent,
        UuidInterface $uuid = null
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

    public function setAdherent(Adherent $adherent): void
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

    public function setStatus(string $status): void
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

    public function isCallMore(): ?bool
    {
        return $this->callMore;
    }

    public function setCallMore(?bool $callMore): void
    {
        $this->callMore = $callMore;
    }

    public function isNeedRenewal(): ?bool
    {
        return $this->needRenewal;
    }

    public function setNeedRenewal(?bool $needRenewal): void
    {
        $this->needRenewal = $needRenewal;
    }

    public function isBecomeCaller(): ?bool
    {
        return $this->becomeCaller;
    }

    public function setBecomeCaller(?bool $becomeCaller): void
    {
        $this->becomeCaller = $becomeCaller;
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
}
