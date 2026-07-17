<?php

declare(strict_types=1);

namespace App\JeMengage\Push\Notification;

use App\Entity\Poll\Poll;
use App\Firebase\Notification\AbstractMulticastNotification;
use App\JeMengage\Push\NotificationScope;

class PollReminderNotification extends AbstractMulticastNotification
{
    public static function create(Poll $poll): self
    {
        return new self(
            '⏳ Plus que quelques heures pour voter !',
            \sprintf('Le vote se termine ce soir, donnez votre avis ! « %s » ', $poll->getShortQuestion()),
            NotificationScope::pollNonVoters($poll->getId()),
        );
    }
}
