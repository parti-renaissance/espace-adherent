<?php

declare(strict_types=1);

namespace App\JeMengage\Push\Notification;

use App\Entity\Poll\Poll;
use App\Firebase\Notification\AbstractMulticastNotification;
use App\JeMengage\Push\NotificationScope;

class PollClosingNotification extends AbstractMulticastNotification
{
    public static function create(Poll $poll): self
    {
        return new self(
            '🚨 Dernière heure pour voter !',
            'Le vote se termine dans 1h. Donnez vite votre avis !',
            NotificationScope::pollNonVoters($poll->getId()),
        );
    }
}
