<?php

declare(strict_types=1);

namespace App\Entity\Renaissance\Adhesion;

use App\Adhesion\AdherentRequestReminderTypeEnum;
use App\Entity\EntityIdentityTrait;
use App\Entity\EntityTimestampableTrait;
use App\Repository\Renaissance\Adhesion\AdherentRequestReminderRepository;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

#[ORM\Entity(repositoryClass: AdherentRequestReminderRepository::class)]
class AdherentRequestReminder
{
    use EntityIdentityTrait;
    use EntityTimestampableTrait;

    #[ORM\Column(type: 'string', enumType: AdherentRequestReminderTypeEnum::class)]
    public AdherentRequestReminderTypeEnum $type;

    #[ORM\JoinColumn(onDelete: 'SET NULL')]
    #[ORM\ManyToOne(targetEntity: AdherentRequest::class)]
    public AdherentRequest $adherentRequest;

    public function __construct(
        UuidInterface $uuid,
        AdherentRequest $adherentRequest,
        AdherentRequestReminderTypeEnum $type,
    ) {
        $this->uuid = $uuid;
        $this->adherentRequest = $adherentRequest;
        $this->type = $type;
    }

    public static function createForAdherentRequest(AdherentRequest $adherentRequest, AdherentRequestReminderTypeEnum $type): self
    {
        return new self(Uuid::uuid4(), $adherentRequest, $type);
    }
}
