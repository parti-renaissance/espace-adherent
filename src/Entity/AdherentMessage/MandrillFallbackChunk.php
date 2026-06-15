<?php

declare(strict_types=1);

namespace App\Entity\AdherentMessage;

use App\Mailchimp\Campaign\Fallback\MandrillFallbackChunkStatusEnum;
use App\Repository\AdherentMessage\MandrillFallbackChunkRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MandrillFallbackChunkRepository::class)]
#[ORM\UniqueConstraint(fields: ['campaign', 'chunkNumber'])]
class MandrillFallbackChunk
{
    #[ORM\Column(type: 'integer', options: ['unsigned' => true])]
    #[ORM\GeneratedValue]
    #[ORM\Id]
    public ?int $id = null;

    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[ORM\ManyToOne]
    public MailchimpCampaign $campaign;

    #[ORM\Column(type: 'integer', options: ['unsigned' => true])]
    public int $chunkNumber;

    #[ORM\Column(enumType: MandrillFallbackChunkStatusEnum::class)]
    public MandrillFallbackChunkStatusEnum $status = MandrillFallbackChunkStatusEnum::Pending;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    public ?\DateTimeImmutable $sentAt = null;

    public function __construct(MailchimpCampaign $campaign, int $chunkNumber)
    {
        $this->campaign = $campaign;
        $this->chunkNumber = $chunkNumber;
    }
}
