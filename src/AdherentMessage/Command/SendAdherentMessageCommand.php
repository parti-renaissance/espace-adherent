<?php

declare(strict_types=1);

namespace App\AdherentMessage\Command;

class SendAdherentMessageCommand
{
    public function __construct(public int $adherentMessageId)
    {
    }
}
