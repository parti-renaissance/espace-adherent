<?php

namespace App\Entity\SmsCampaign;

use App\Entity\EntityIdentityTrait;
use App\Entity\EntityTimestampableTrait;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;

/**
 * @ORM\Entity
 */
class SmsStopHistory
{
    use EntityIdentityTrait;
    use EntityTimestampableTrait;

    /** @ORM\Column(type="datetime", nullable=true) */
    public ?\DateTime $eventDate = null;

    /** @ORM\Column(type="integer", nullable=true) */
    public ?int $campaignExternalId = null;

    /** @ORM\Column(nullable=true) */
    public ?string $receiver = null;

    public function __construct(?\DateTime $eventDate, ?int $campaignExternalId, ?string $receiver)
    {
        $this->uuid = Uuid::uuid4();
        $this->eventDate = $eventDate;
        $this->campaignExternalId = $campaignExternalId;
        $this->receiver = $receiver;
    }
}
