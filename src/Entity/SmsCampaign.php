<?php

namespace App\Entity;

use App\Entity\Audience\AudienceSnapshot;
use App\SmsCampaign\SmsCampaignStatusEnum;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 */
class SmsCampaign
{
    use EntityIdentityTrait;
    use EntityTimestampableTrait;
    use EntityAdministratorTrait;

    /**
     * @var string
     *
     * @ORM\Column
     *
     * @Assert\NotBlank
     */
    private $title;

    /**
     * @var string
     *
     * @ORM\Column(type="text")
     *
     * @Assert\NotBlank
     * @Assert\Length(max=149)
     */
    private $content;

    /**
     * @var AudienceSnapshot
     *
     * @ORM\OneToOne(targetEntity="App\Entity\Audience\AudienceSnapshot", cascade={"persist"})
     * @ORM\JoinColumn(onDelete="CASCADE")
     *
     * @Assert\NotBlank
     */
    private $audience;

    /**
     * @var string
     *
     * @ORM\Column
     */
    private $status = SmsCampaignStatusEnum::DRAFT;

    public function __construct(UuidInterface $uuid = null)
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
}
