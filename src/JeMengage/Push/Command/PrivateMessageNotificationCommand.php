<?php

declare(strict_types=1);

namespace App\JeMengage\Push\Command;

use App\Entity\TimelineItemPrivateMessage;

class PrivateMessageNotificationCommand extends AbstractSendNotificationCommand
{
    public function getClass(): string
    {
        return TimelineItemPrivateMessage::class;
    }
}
