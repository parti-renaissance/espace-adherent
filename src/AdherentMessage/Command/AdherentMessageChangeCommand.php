<?php

namespace App\AdherentMessage\Command;

use App\Mailchimp\CampaignMessageInterface;
use Ramsey\Uuid\UuidInterface;

class AdherentMessageChangeCommand implements CampaignMessageInterface
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
}
