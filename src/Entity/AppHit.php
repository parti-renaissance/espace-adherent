<?php

namespace App\Entity;

use App\AppSession\SystemEnum;
use App\JeMengage\Hit\EventTypeEnum;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity]
class AppHit
{
    use EntityIdentityTrait;
    use EntityTimestampableTrait;
    use EntityUTMTrait;

    #[Groups(['hit:write'])]
    #[ORM\Column(enumType: EventTypeEnum::class)]
    public EventTypeEnum $eventType;

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
    #[ORM\Column(nullable: true)]
    public ?string $objectType = null;

    #[Groups(['hit:write'])]
    #[ORM\Column(nullable: true)]
    public ?string $objectId = null;

    #[Groups(['hit:write'])]
    #[ORM\Column(nullable: true)]
    public ?string $source = null;

    #[Groups(['hit:write'])]
    #[ORM\Column(nullable: true)]
    public ?string $buttonName = null;

    #[Groups(['hit:write'])]
    #[ORM\Column(type: 'text', nullable: true)]
    public ?string $targetUrl = null;

    #[Groups(['hit:write'])]
    #[ORM\Column(type: 'text', nullable: true)]
    public ?string $userAgent = null;

    #[Groups(['hit:write'])]
    #[ORM\Column(nullable: true, enumType: SystemEnum::class)]
    public ?SystemEnum $appSystem = null;

    #[Groups(['hit:write'])]
    #[ORM\Column]
    public ?string $appVersion = null;

    #[Groups(['hit:write'])]
    #[ORM\Column(type: 'datetime')]
    public \DateTimeInterface $appDate;

    #[ORM\Column(type: 'json', options: ['jsonb' => true])]
    public array $raw = [];

    public function __construct()
    {
        $this->uuid = Uuid::uuid4();
    }
}
