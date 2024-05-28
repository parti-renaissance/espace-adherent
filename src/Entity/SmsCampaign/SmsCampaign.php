<?php

namespace App\Entity\SmsCampaign;

use App\Entity\Audience\AudienceSnapshot;
use App\Entity\EntityAdministratorTrait;
use App\Entity\EntityIdentityTrait;
use App\Entity\EntityTimestampableTrait;
use App\Repository\SmsCampaignRepository;
use App\SmsCampaign\SmsCampaignStatusEnum;
use App\SmsCampaign\Statistics;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: SmsCampaignRepository::class)]
class SmsCampaign
{
    use EntityIdentityTrait;
    use EntityTimestampableTrait;
    use EntityAdministratorTrait;

    /**
     * @var string
     *
     * @Assert\NotBlank
     */
    #[ORM\Column]
    private $title;

    /**
     * @var string
     *
     * @Assert\NotBlank
     * @Assert\Length(max=149)
     */
    #[ORM\Column(type: 'text')]
    private $content;

    /**
     * @var AudienceSnapshot
     *
     * @Assert\NotBlank
     */
    #[ORM\JoinColumn(onDelete: 'CASCADE')]
    #[ORM\OneToOne(targetEntity: AudienceSnapshot::class, cascade: ['persist'], orphanRemoval: true)]
    private $audience;

    /**
     * @var string
     */
    #[ORM\Column]
    private $status = SmsCampaignStatusEnum::DRAFT;

    /**
     * @var int|null
     */
    #[ORM\Column(type: 'integer', nullable: true)]
    private $recipientCount;

    /**
     * @var int|null
     */
    #[ORM\Column(type: 'integer', nullable: true)]
    private $adherentCount;

    /**
     * @var string|null
     */
    #[ORM\Column(type: 'text', nullable: true)]
    private $responsePayload;

    /**
     * @var string|null
     */
    #[ORM\Column(nullable: true)]
    private $externalId;

    /**
     * @var \DateTime|null
     */
    #[ORM\Column(type: 'datetime', nullable: true)]
    private $sentAt;

    public ?Statistics $statistics = null;

    public function __construct(?UuidInterface $uuid = null)
    {
        $this->uuid = $uuid ?? Uuid::uuid4();
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): void
    {
        $this->content = $content;
    }

    public function __toString(): string
    {
        return (string) $this->title;
    }

    public function getAudience(): ?AudienceSnapshot
    {
        return $this->audience;
    }

    public function setAudience(AudienceSnapshot $audience): void
    {
        $this->audience = $audience;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): void
    {
        $this->status = $status;
    }

    public function isDraft(): bool
    {
        return SmsCampaignStatusEnum::DRAFT === $this->status;
    }

    public function isDone(): bool
    {
        return SmsCampaignStatusEnum::DONE === $this->status;
    }

    public function isSending(): bool
    {
        return SmsCampaignStatusEnum::SENDING === $this->status;
    }

    public function getRecipientCount(): ?int
    {
        return $this->recipientCount;
    }

    public function setRecipientCount(?int $recipientCount): void
    {
        $this->recipientCount = $recipientCount;
    }

    public function getAdherentCount(): ?int
    {
        return $this->adherentCount;
    }

    public function setAdherentCount(?int $adherentCount): void
    {
        $this->adherentCount = $adherentCount;
    }

    public function setResponsePayload(?string $responsePayload): void
    {
        $this->responsePayload = $responsePayload;
    }

    public function getResponsePayload(): ?string
    {
        return $this->responsePayload;
    }

    public function setExternalId(?string $externalId): void
    {
        $this->externalId = $externalId;
    }

    public function getExternalId(): ?string
    {
        return $this->externalId;
    }

    public function getSentAt(): ?\DateTime
    {
        return $this->sentAt;
    }

    public function send(): void
    {
        $this->status = SmsCampaignStatusEnum::SENDING;
        $this->sentAt = new \DateTime();
    }
}
