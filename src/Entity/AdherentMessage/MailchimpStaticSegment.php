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
    public ?int $id = null;

    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[ORM\OneToOne(inversedBy: 'mailchimpStaticSegment')]
    public MailchimpCampaign $campaign;

    #[ORM\Column(type: 'integer', nullable: true)]
    public ?int $mailchimpSegmentId = null;

    #[ORM\Column(nullable: true)]
    public ?string $name = null;

    #[ORM\Column(type: 'json', nullable: true)]
    public ?array $filterSnapshot = null;

    #[ORM\Column(length: 64, nullable: true)]
    public ?string $filterHash = null;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    public ?\DateTimeImmutable $buildStartedAt = null;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    public ?\DateTimeImmutable $builtAt = null;

    #[ORM\Column(type: 'integer', nullable: true, options: ['unsigned' => true])]
    public ?int $buildDurationMs = null;

    #[ORM\Column(type: 'integer', nullable: true, options: ['unsigned' => true])]
    public ?int $expectedCount = null;

    #[ORM\Column(type: 'integer', nullable: true, options: ['unsigned' => true])]
    public ?int $preparedCount = null;

    #[ORM\Column(type: 'integer', nullable: true, options: ['unsigned' => true])]
    public ?int $erroredCount = null;

    #[ORM\Column(type: 'integer', nullable: true, options: ['unsigned' => true])]
    public ?int $refusedCount = null;

    #[ORM\Column(type: 'integer', nullable: true, options: ['unsigned' => true])]
    public ?int $chunksTotal = null;

    #[ORM\Column(type: 'integer', options: ['unsigned' => true, 'default' => 0])]
    public int $chunksDone = 0;

    #[ORM\Column(type: 'integer', options: ['unsigned' => true, 'default' => 0])]
    public int $attempts = 0;

    #[ORM\Column(type: 'text', nullable: true)]
    public ?string $errorSummary = null;

    public function __construct(MailchimpCampaign $campaign)
    {
        $this->campaign = $campaign;
    }

    public function startNewRun(): void
    {
        ++$this->attempts;
        $this->buildStartedAt = new \DateTimeImmutable();
        $this->builtAt = null;
        $this->buildDurationMs = null;
        $this->expectedCount = null;
        $this->preparedCount = null;
        $this->refusedCount = null;
        $this->erroredCount = null;
        $this->chunksTotal = null;
        $this->chunksDone = 0;
        $this->errorSummary = null;
    }
}
