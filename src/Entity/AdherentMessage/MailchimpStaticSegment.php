<?php

declare(strict_types=1);

namespace App\Entity\AdherentMessage;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class MailchimpStaticSegment
{
    #[ORM\Column(type: 'integer', options: ['unsigned' => true])]
    #[ORM\GeneratedValue]
    #[ORM\Id]
    private ?int $id = null;

    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[ORM\OneToOne(inversedBy: 'mailchimpStaticSegment')]
    private MailchimpCampaign $campaign;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $mailchimpSegmentId = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $name = null;

    #[ORM\Column(type: 'json', nullable: true)]
    private ?array $filterSnapshot = null;

    #[ORM\Column(length: 64, nullable: true)]
    private ?string $filterHash = null;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $builtAt = null;

    #[ORM\Column(type: 'integer', nullable: true, options: ['unsigned' => true])]
    private ?int $buildDurationMs = null;

    #[ORM\Column(type: 'integer', nullable: true, options: ['unsigned' => true])]
    private ?int $expectedCount = null;

    #[ORM\Column(type: 'integer', nullable: true, options: ['unsigned' => true])]
    private ?int $preparedCount = null;

    #[ORM\Column(type: 'integer', nullable: true, options: ['unsigned' => true])]
    private ?int $erroredCount = null;

    #[ORM\Column(type: 'integer', nullable: true, options: ['unsigned' => true])]
    private ?int $refusedCount = null;

    #[ORM\Column(type: 'integer', nullable: true, options: ['unsigned' => true])]
    private ?int $chunksTotal = null;

    #[ORM\Column(type: 'integer', options: ['unsigned' => true, 'default' => 0])]
    private int $chunksDone = 0;

    #[ORM\Column(type: 'integer', options: ['unsigned' => true, 'default' => 0])]
    private int $attempts = 0;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $errorSummary = null;

    public function __construct(MailchimpCampaign $campaign)
    {
        $this->campaign = $campaign;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCampaign(): MailchimpCampaign
    {
        return $this->campaign;
    }

    public function getMailchimpSegmentId(): ?int
    {
        return $this->mailchimpSegmentId;
    }

    public function setMailchimpSegmentId(?int $mailchimpSegmentId): void
    {
        $this->mailchimpSegmentId = $mailchimpSegmentId;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    public function getFilterSnapshot(): ?array
    {
        return $this->filterSnapshot;
    }

    public function setFilterSnapshot(?array $filterSnapshot): void
    {
        $this->filterSnapshot = $filterSnapshot;
    }

    public function getFilterHash(): ?string
    {
        return $this->filterHash;
    }

    public function setFilterHash(?string $filterHash): void
    {
        $this->filterHash = $filterHash;
    }

    public function getBuiltAt(): ?\DateTimeImmutable
    {
        return $this->builtAt;
    }

    public function setBuiltAt(?\DateTimeImmutable $builtAt): void
    {
        $this->builtAt = $builtAt;
    }

    public function getBuildDurationMs(): ?int
    {
        return $this->buildDurationMs;
    }

    public function setBuildDurationMs(?int $buildDurationMs): void
    {
        $this->buildDurationMs = $buildDurationMs;
    }

    public function getExpectedCount(): ?int
    {
        return $this->expectedCount;
    }

    public function setExpectedCount(?int $expectedCount): void
    {
        $this->expectedCount = $expectedCount;
    }

    public function getPreparedCount(): ?int
    {
        return $this->preparedCount;
    }

    public function setPreparedCount(?int $preparedCount): void
    {
        $this->preparedCount = $preparedCount;
    }

    public function getErroredCount(): ?int
    {
        return $this->erroredCount;
    }

    public function setErroredCount(?int $erroredCount): void
    {
        $this->erroredCount = $erroredCount;
    }

    public function getRefusedCount(): ?int
    {
        return $this->refusedCount;
    }

    public function setRefusedCount(?int $refusedCount): void
    {
        $this->refusedCount = $refusedCount;
    }

    public function getChunksTotal(): ?int
    {
        return $this->chunksTotal;
    }

    public function setChunksTotal(?int $chunksTotal): void
    {
        $this->chunksTotal = $chunksTotal;
    }

    public function getChunksDone(): int
    {
        return $this->chunksDone;
    }

    public function setChunksDone(int $chunksDone): void
    {
        $this->chunksDone = $chunksDone;
    }

    public function incrementChunksDone(): void
    {
        ++$this->chunksDone;
    }

    public function getAttempts(): int
    {
        return $this->attempts;
    }

    public function incrementAttempts(): void
    {
        ++$this->attempts;
    }

    public function getErrorSummary(): ?string
    {
        return $this->errorSummary;
    }

    public function setErrorSummary(?string $errorSummary): void
    {
        $this->errorSummary = $errorSummary;
    }
}
