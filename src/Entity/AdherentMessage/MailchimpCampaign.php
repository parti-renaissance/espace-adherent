<?php

declare(strict_types=1);

namespace App\Entity\AdherentMessage;

use App\AdherentMessage\AdherentMessageSynchronizedObjectInterface;
use App\AdherentMessage\MailchimpStatusEnum;
use App\Entity\Geo\Zone;
use App\Entity\MailchimpSegment;
use App\Mailchimp\Campaign\Audience\AudienceCheckEnum;
use App\Mailchimp\Campaign\Audience\BlockReasonEnum;
use App\Mailchimp\Campaign\Audience\PreparationStatusEnum;
use App\Repository\MailchimpCampaignRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Timestampable;
use Gedmo\Timestampable\Traits\TimestampableEntity;

#[ORM\Entity(repositoryClass: MailchimpCampaignRepository::class)]
class MailchimpCampaign implements AdherentMessageSynchronizedObjectInterface, Timestampable
{
    use TimestampableEntity;

    /**
     * @var int|null
     */
    #[ORM\Column(type: 'integer', options: ['unsigned' => true])]
    #[ORM\GeneratedValue]
    #[ORM\Id]
    protected $id;

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @var MailchimpCampaignReport|null
     */
    #[ORM\OneToOne(targetEntity: MailchimpCampaignReport::class, cascade: ['all'])]
    private $report;

    /**
     * @var string
     */
    #[ORM\Column(nullable: true)]
    private $externalId;

    /**
     * @var bool
     */
    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private $synchronized = false;

    /**
     * @var int|null
     */
    #[ORM\Column(type: 'integer', nullable: true)]
    private $recipientCount;

    /**
     * @var AdherentMessageInterface|AdherentMessage
     */
    #[ORM\JoinColumn(onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: AdherentMessage::class, inversedBy: 'mailchimpCampaigns')]
    private $message;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $staticSegmentId = null;

    /**
     * @var string|null
     */
    #[ORM\Column(nullable: true)]
    private $label;

    #[ORM\Column(enumType: MailchimpStatusEnum::class)]
    public MailchimpStatusEnum $status = MailchimpStatusEnum::Save;

    #[ORM\Column(type: 'smallint', options: ['unsigned' => true, 'default' => 0])]
    private int $retryCount = 0;

    #[ORM\Column(type: 'json', nullable: true)]
    private ?array $retryHistory = null;

    /**
     * @var string|null
     */
    #[ORM\Column(nullable: true)]
    private $detail;

    /**
     * @var string|null
     */
    #[ORM\Column(nullable: true)]
    private $city;

    /**
     * @var MailchimpSegment[]|Collection
     */
    #[ORM\ManyToMany(targetEntity: MailchimpSegment::class, cascade: ['all'])]
    private $mailchimpSegments;

    #[ORM\Column(nullable: true)]
    private ?string $mailchimpListType = null;

    #[ORM\ManyToOne]
    private ?Zone $zone = null;

    #[ORM\Column(length: 20, enumType: PreparationStatusEnum::class, options: ['default' => PreparationStatusEnum::NotStarted->value])]
    private PreparationStatusEnum $preparationStatus = PreparationStatusEnum::NotStarted;

    #[ORM\Column(nullable: true)]
    private ?string $mailchimpSegmentName = null;

    #[ORM\Column(type: 'integer', nullable: true, options: ['unsigned' => true])]
    private ?int $expectedAudienceCount = null;

    #[ORM\Column(type: 'integer', nullable: true, options: ['unsigned' => true])]
    private ?int $preparedAudienceCount = null;

    #[ORM\Column(length: 20, enumType: AudienceCheckEnum::class, nullable: true)]
    private ?AudienceCheckEnum $audienceCheck = null;

    #[ORM\Column(length: 50, enumType: BlockReasonEnum::class, nullable: true)]
    private ?BlockReasonEnum $blockReason = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $preparedAt = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $preparationLockedBy = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $preparationFailureDetail = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $deleteSegmentAt = null;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private bool $cancellationRequested = false;

    #[ORM\Column(type: 'integer', options: ['default' => 0, 'unsigned' => true])]
    private int $preparedChunksDone = 0;

    #[ORM\OneToOne(mappedBy: 'campaign', targetEntity: MailchimpStaticSegment::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private ?MailchimpStaticSegment $mailchimpStaticSegment = null;

    public function __construct(AdherentMessageInterface $message)
    {
        $this->message = $message;
        $this->mailchimpSegments = new ArrayCollection();
    }

    public function getExternalId(): ?string
    {
        return $this->externalId;
    }

    public function setExternalId(string $externalId): void
    {
        $this->externalId = $externalId;
    }

    public function setSynchronized(bool $value): void
    {
        $this->synchronized = $value;
    }

    public function isSynchronized(): bool
    {
        return $this->synchronized;
    }

    public function getRecipientCount(): ?int
    {
        return $this->recipientCount;
    }

    public function setRecipientCount(?int $recipientCount): void
    {
        $this->recipientCount = $recipientCount;
    }

    public function getMessage(): AdherentMessageInterface
    {
        return $this->message;
    }

    public function getStaticSegmentId(): ?int
    {
        return $this->staticSegmentId;
    }

    public function setStaticSegmentId(?int $staticSegmentId): void
    {
        $this->staticSegmentId = $staticSegmentId;
    }

    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function setLabel(?string $label): void
    {
        $this->label = $label;
    }

    /**
     * @return MailchimpSegment[]
     */
    public function getMailchimpSegments(): array
    {
        return $this->mailchimpSegments->toArray();
    }

    public function addMailchimpSegment(MailchimpSegment $mailchimpSegment): void
    {
        if (!$this->mailchimpSegments->contains($mailchimpSegment)) {
            $this->mailchimpSegments->add($mailchimpSegment);
        }
    }

    public function removeMailchimpSegment(MailchimpSegment $mailchimpSegment): void
    {
        $this->mailchimpSegments->removeElement($mailchimpSegment);
    }

    public function markAsSending(): void
    {
        $this->status = MailchimpStatusEnum::Sending;
    }

    public function markAsError(?string $errorMessage = null): void
    {
        $this->status = MailchimpStatusEnum::Error;
        $this->detail = $errorMessage;
    }

    public function getDetail(): ?string
    {
        return $this->detail;
    }

    public function getRetryCount(): int
    {
        return $this->retryCount;
    }

    public function incrementRetryCount(): void
    {
        ++$this->retryCount;
    }

    public function addRetryAttempt(bool $success, ?string $detail = null): void
    {
        $this->retryHistory[] = [
            'at' => new \DateTimeImmutable()->format('c'),
            'success' => $success,
            'detail' => $detail,
        ];
    }

    public function getRetryHistory(): array
    {
        return $this->retryHistory ?? [];
    }

    public function reset(): void
    {
        $this->synchronized = false;

        $this->recipientCount =
        $this->label =
        $this->city =
        $this->staticSegmentId = null;
    }

    public function getReport(): ?MailchimpCampaignReport
    {
        return $this->report;
    }

    public function setReport(?MailchimpCampaignReport $report): void
    {
        $this->report = $report;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(?string $city): void
    {
        $this->city = $city;
    }

    public function resetFilter(): void
    {
        $this->staticSegmentId = $this->city = $this->mailchimpListType = null;
        $this->mailchimpSegments = new ArrayCollection();
    }

    public function getMailchimpListType(): ?string
    {
        return $this->mailchimpListType;
    }

    public function setMailchimpListType(?string $mailchimpListType): void
    {
        $this->mailchimpListType = $mailchimpListType;
    }

    public function getZone(): ?Zone
    {
        return $this->zone;
    }

    public function setZone(?Zone $zone): void
    {
        $this->zone = $zone;
    }

    public function getPreparationStatus(): PreparationStatusEnum
    {
        return $this->preparationStatus;
    }

    public function getMailchimpSegmentName(): ?string
    {
        return $this->mailchimpSegmentName;
    }

    public function setMailchimpSegmentName(?string $name): void
    {
        $this->mailchimpSegmentName = $name;
    }

    public function getExpectedAudienceCount(): ?int
    {
        return $this->expectedAudienceCount;
    }

    public function setExpectedAudienceCount(?int $count): void
    {
        $this->expectedAudienceCount = $count;
    }

    public function getPreparedAudienceCount(): ?int
    {
        return $this->preparedAudienceCount;
    }

    public function getAudienceCheck(): ?AudienceCheckEnum
    {
        return $this->audienceCheck;
    }

    public function getBlockReason(): ?BlockReasonEnum
    {
        return $this->blockReason;
    }

    public function getPreparedAt(): ?\DateTimeInterface
    {
        return $this->preparedAt;
    }

    public function getPreparationLockedBy(): ?string
    {
        return $this->preparationLockedBy;
    }

    public function getPreparationFailureDetail(): ?string
    {
        return $this->preparationFailureDetail;
    }

    public function getDeleteSegmentAt(): ?\DateTimeInterface
    {
        return $this->deleteSegmentAt;
    }

    public function setDeleteSegmentAt(?\DateTimeInterface $deleteSegmentAt): void
    {
        $this->deleteSegmentAt = $deleteSegmentAt;
    }

    public function isCancellationRequested(): bool
    {
        return $this->cancellationRequested;
    }

    public function getPreparedChunksDone(): int
    {
        return $this->preparedChunksDone;
    }

    public function canSend(): bool
    {
        if (PreparationStatusEnum::Ready !== $this->preparationStatus) {
            return false;
        }

        if (null !== $this->blockReason) {
            return false;
        }

        if (null === $this->audienceCheck || AudienceCheckEnum::Mismatch === $this->audienceCheck) {
            return false;
        }

        return !$this->message instanceof AdherentMessage || !$this->message->isSent();
    }

    public function markAsPreparing(string $lockedBy): void
    {
        $this->preparationStatus = PreparationStatusEnum::Preparing;
        $this->preparationLockedBy = $lockedBy;
        $this->blockReason = null;
        $this->audienceCheck = null;
        $this->expectedAudienceCount = null;
        $this->preparedAudienceCount = null;
        $this->preparedAt = null;
        $this->preparationFailureDetail = null;
        $this->cancellationRequested = false;
        $this->preparedChunksDone = 0;
    }

    public function markAsReady(int $expected, int $prepared, AudienceCheckEnum $audienceCheck): void
    {
        $this->preparationStatus = PreparationStatusEnum::Ready;
        $this->expectedAudienceCount = $expected;
        $this->preparedAudienceCount = $prepared;
        $this->audienceCheck = $audienceCheck;
        $this->preparedAt = new \DateTime();
    }

    public function markAsFailed(BlockReasonEnum $blockReason, ?string $detail = null): void
    {
        $this->preparationStatus = PreparationStatusEnum::Failed;
        $this->blockReason = $blockReason;
        $this->preparationFailureDetail = $detail;
    }

    public function requestCancellation(): void
    {
        $this->cancellationRequested = true;
    }

    public function incrementChunksDone(): void
    {
        ++$this->preparedChunksDone;
    }

    public function getMailchimpStaticSegment(): ?MailchimpStaticSegment
    {
        return $this->mailchimpStaticSegment;
    }

    public function setMailchimpStaticSegment(?MailchimpStaticSegment $segment): void
    {
        $this->mailchimpStaticSegment = $segment;
    }
}
