<?php

declare(strict_types=1);

namespace App\Entity;

use App\AppSession\SystemEnum;
use App\JeMengage\Hit\EventTypeEnum;
use App\JeMengage\Hit\SourceGroupEnum;
use App\JeMengage\Hit\TargetTypeEnum;
use App\Repository\AppHitRepository;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: AppHitRepository::class)]
#[ORM\Index(fields: ['eventType'])]
#[ORM\Index(fields: ['source'])]
#[ORM\Index(fields: ['sourceGroup'])]
#[ORM\Index(fields: ['eventType', 'source'])]
#[ORM\Index(fields: ['eventType', 'sourceGroup'])]
#[ORM\Index(fields: ['objectType'])]
#[ORM\Index(fields: ['objectId'])]
class AppHit
{
    use EntityIdentityTrait;
    use EntityTimestampableTrait;
    use EntityUTMTrait;

    #[Groups(['hit:write'])]
    #[ORM\Column(enumType: EventTypeEnum::class)]
    public EventTypeEnum $eventType;

    #[Groups(['hit:read'])]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[ORM\ManyToOne]
    public ?Adherent $adherent = null;

    #[ORM\JoinColumn(onDelete: 'SET NULL')]
    #[ORM\ManyToOne]
    public ?Adherent $referrer = null;

    #[Groups(['hit:write'])]
    #[ORM\Column(nullable: true)]
    public ?string $referrerCode = null;

    #[ORM\JoinColumn(onDelete: 'SET NULL')]
    #[ORM\ManyToOne]
    public ?AppSession $appSession = null;

    #[Groups(['hit:write'])]
    #[ORM\Column(type: 'uuid')]
    public ?UuidInterface $activitySessionUuid = null;

    #[Groups(['hit:write'])]
    #[ORM\Column(nullable: true)]
    public ?string $openType = null;

    #[Groups(['hit:write'])]
    #[ORM\Column(nullable: true, enumType: TargetTypeEnum::class)]
    public ?TargetTypeEnum $objectType = null;

    #[Groups(['hit:write'])]
    #[ORM\Column(nullable: true)]
    public ?string $objectId = null;

    #[Groups(['hit:write', 'hit:open:read'])]
    #[ORM\Column(nullable: true)]
    public ?string $source = null;

    #[ORM\Column(nullable: true, enumType: SourceGroupEnum::class)]
    public ?SourceGroupEnum $sourceGroup = SourceGroupEnum::App;

    #[Groups(['hit:write', 'hit:click:read'])]
    #[ORM\Column(nullable: true)]
    public ?string $buttonName = null;

    #[Groups(['hit:write', 'hit:click:read'])]
    #[ORM\Column(type: 'text', nullable: true)]
    public ?string $targetUrl = null;

    #[Groups(['hit:write'])]
    #[ORM\Column(type: 'text', nullable: true)]
    public ?string $userAgent = null;

    #[Groups(['hit:write', 'hit:read'])]
    #[ORM\Column(nullable: true, enumType: SystemEnum::class)]
    public ?SystemEnum $appSystem = null;

    #[Groups(['hit:write'])]
    #[ORM\Column(nullable: true)]
    public ?string $appVersion = null;

    #[Groups(['hit:write'])]
    #[ORM\Column(type: 'datetime')]
    public \DateTimeInterface $appDate;

    #[ORM\Column(unique: true, nullable: true)]
    public ?string $fingerprint = null;

    #[ORM\Column(type: 'json', nullable: true, options: ['jsonb' => true])]
    public array $raw = [];

    public function __construct()
    {
        $this->uuid = Uuid::uuid4();
    }

    public function isImpression(): bool
    {
        return EventTypeEnum::Impression === $this->eventType;
    }

    public function updateFingerprintHash(): void
    {
        $this->fingerprint = hash('sha256', implode('|', array_filter([
            $this->adherent?->getId(),
            $this->activitySessionUuid->toString(),
            $this->eventType->value,
            $this->objectType?->value,
            $this->objectId,
            $this->source,
            $this->appDate->format('Y-m-d H:i:s'),
        ])));
    }
}
