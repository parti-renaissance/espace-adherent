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
            '⏳ Encore quelques heures !',
            \sprintf('Participez ce soir au sondage « %s ». ', $poll->getShortQuestion()),
            NotificationScope::pollNonVoters($poll->getId()),
        );
    }
}
