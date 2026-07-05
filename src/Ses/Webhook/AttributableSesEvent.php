<?php

declare(strict_types=1);

namespace App\Ses\Webhook;

use Symfony\Component\Uid\Uuid;

interface AttributableSesEvent
{
    public Uuid $campaignUuid { get; }
    public Uuid $adherentUuid { get; }
}
