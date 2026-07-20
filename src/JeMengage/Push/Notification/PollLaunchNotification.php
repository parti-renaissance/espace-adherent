<?php

declare(strict_types=1);

namespace App\JeMengage\Push\Notification;

use App\Entity\Poll\Poll;
use App\Firebase\Notification\AbstractMulticastNotification;
use App\JeMengage\Push\NotificationScope;

class PollLaunchNotification extends AbstractMulticastNotification
{
    public static function create(Poll $poll): self
    {
        return new self(
            '🗳️ Question de la semaine !',
            \sprintf('👉 Donnez votre avis ! %s', $poll->getShortQuestion()),
            NotificationScope::national(),
        );
    }
}
