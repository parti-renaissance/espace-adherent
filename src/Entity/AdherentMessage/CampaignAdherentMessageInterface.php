<?php

namespace App\Entity\AdherentMessage;

use Ramsey\Uuid\UuidInterface;

interface CampaignAdherentMessageInterface
{
    public function getUuid(): UuidInterface;
}
