<?php

declare(strict_types=1);

namespace App\AdherentMessage\Command;

use App\Mailchimp\AbstractCampaignMessage;
use Ramsey\Uuid\UuidInterface;

class AdherentMessageChangeCommand extends AbstractCampaignMessage
{
    private $uuid;

    public function __construct(UuidInterface $uuid)
    {
        $this->uuid = $uuid;
    }

    public function getUuid(): UuidInterface
    {
        return $this->uuid;
    }

    public function getLockKey(): string
    {
        return 'sync_adherent_message_'.$this->uuid->toString();
    }
}
