<?php

declare(strict_types=1);

namespace App\Entity\AdherentMessage;

use App\Entity\Adherent;
use App\Mailchimp\Campaign\Audience\SegmentMemberStatusEnum;
use App\Repository\AdherentMessage\MailchimpStaticSegmentMemberRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MailchimpStaticSegmentMemberRepository::class)]
#[ORM\Index(fields: ['staticSegment'])]
#[ORM\Index(fields: ['staticSegment', 'chunkNumber'])]
#[ORM\Index(fields: ['staticSegment', 'processingStatus'])]
#[ORM\UniqueConstraint(fields: ['adherent', 'staticSegment'])]
class MailchimpStaticSegmentMember
{
    #[ORM\Column(type: 'integer', options: ['unsigned' => true])]
    #[ORM\GeneratedValue]
    #[ORM\Id]
    public ?int $id = null;

    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[ORM\ManyToOne]
    public MailchimpStaticSegment $staticSegment;

    #[ORM\JoinColumn(onDelete: 'SET NULL')]
    #[ORM\ManyToOne]
    public ?Adherent $adherent = null;

    #[ORM\Column(type: 'integer', options: ['unsigned' => true, 'default' => 0])]
    public int $chunkNumber = 0;

    #[ORM\Column(enumType: SegmentMemberStatusEnum::class, options: ['default' => SegmentMemberStatusEnum::Pending->value])]
    public SegmentMemberStatusEnum $processingStatus = SegmentMemberStatusEnum::Pending;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    public ?\DateTimeImmutable $processedAt = null;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    public ?\DateTimeImmutable $deliveredAt = null;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    public ?\DateTimeImmutable $delayedAt = null;

    #[ORM\Column(nullable: true)]
    public ?string $delayType = null;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    public ?\DateTimeImmutable $rejectedAt = null;

    #[ORM\Column(nullable: true)]
    public ?string $rejectReason = null;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    public ?\DateTimeImmutable $bouncedAt = null;

    #[ORM\Column(nullable: true)]
    public ?string $bounceSubType = null;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    public ?\DateTimeImmutable $complainedAt = null;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    public ?\DateTimeImmutable $unsubscribedAt = null;

    #[ORM\Column(type: 'text', nullable: true)]
    public ?string $errorMessage = null;

    #[ORM\Column(type: 'datetime')]
    public \DateTimeInterface $createdAt;

    public function __construct(
        MailchimpStaticSegment $staticSegment,
        ?Adherent $adherent,
        int $chunkNumber = 0,
        ?\DateTimeInterface $createdAt = null,
    ) {
        $this->staticSegment = $staticSegment;
        $this->adherent = $adherent;
        $this->chunkNumber = $chunkNumber;
        $this->createdAt = $createdAt ?? new \DateTime();
    }
}
