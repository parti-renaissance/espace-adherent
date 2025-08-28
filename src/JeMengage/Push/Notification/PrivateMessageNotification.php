<?php

namespace App\JeMengage\Push\Notification;

use App\Entity\TimelineItemPrivateMessage;
use App\Firebase\Notification\AbstractMulticastNotification;

class PrivateMessageNotification extends AbstractMulticastNotification
{
    public static function create(TimelineItemPrivateMessage $privateMessage): self
    {
        if (!$privateMessage->notificationTitle || !$privateMessage->notificationDescription) {
            throw new \InvalidArgumentException(\sprintf('Private message #%d must have title and description to create a notification.', $privateMessage->getId()));
        }

        return new self($privateMessage->notificationTitle, $privateMessage->notificationDescription);
    }
}
