<?php

declare(strict_types=1);

namespace App\AdherentMessage\Command;

use App\Mailchimp\AbstractCampaignMessage;
use Symfony\Component\Uid\Uuid;

class AdherentMessageChangeCommand extends AbstractCampaignMessage
{
    private $uuid;

    public function __construct(Uuid $uuid)
    {
        $this->uuid = $uuid;
    }

    public function getUuid(): Uuid
    {
        return $this->uuid;
    }

    public function getLockKey(): string
    {
        return 'sync_adherent_message_'.$this->uuid->toRfc4122();
    }
}
