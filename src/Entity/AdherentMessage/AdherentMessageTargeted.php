<?php

declare(strict_types=1);

namespace App\Entity\AdherentMessage;

use App\Entity\Adherent;
use App\Mailchimp\Campaign\Audience\TargetedProcessingStatusEnum;
use App\Repository\AdherentMessage\AdherentMessageTargetedRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AdherentMessageTargetedRepository::class)]
#[ORM\Index(fields: ['message'])]
#[ORM\Index(fields: ['message', 'chunkNumber'])]
#[ORM\Index(fields: ['message', 'processingStatus'])]
#[ORM\UniqueConstraint(fields: ['adherent', 'message'])]
class AdherentMessageTargeted
{
    #[ORM\Column(type: 'integer', options: ['unsigned' => true])]
    #[ORM\GeneratedValue]
    #[ORM\Id]
    public ?int $id = null;

    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[ORM\ManyToOne]
    public AdherentMessage $message;

    #[ORM\JoinColumn(onDelete: 'SET NULL')]
    #[ORM\ManyToOne]
    public ?Adherent $adherent = null;

    #[ORM\Column(type: 'integer', options: ['unsigned' => true, 'default' => 0])]
    public int $chunkNumber = 0;

    #[ORM\Column(enumType: TargetedProcessingStatusEnum::class, options: ['default' => TargetedProcessingStatusEnum::Pending->value])]
    public TargetedProcessingStatusEnum $processingStatus = TargetedProcessingStatusEnum::Pending;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    public ?\DateTimeImmutable $processedAt = null;

    #[ORM\Column(type: 'text', nullable: true)]
    public ?string $errorMessage = null;

    #[ORM\Column(type: 'datetime')]
    public \DateTimeInterface $targetedAt;

    public function __construct(
        AdherentMessage $message,
        ?Adherent $adherent,
        int $chunkNumber = 0,
        ?\DateTimeInterface $targetedAt = null,
    ) {
        $this->message = $message;
        $this->adherent = $adherent;
        $this->chunkNumber = $chunkNumber;
        $this->targetedAt = $targetedAt ?? new \DateTimeImmutable();
    }
}
