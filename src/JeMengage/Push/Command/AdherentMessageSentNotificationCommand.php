<?php

declare(strict_types=1);

namespace App\JeMengage\Push\Command;

use App\Entity\AdherentMessage\AdherentMessage;

class AdherentMessageSentNotificationCommand extends AbstractSendNotificationCommand
{
    public function getClass(): string
    {
        return AdherentMessage::class;
    }
}
