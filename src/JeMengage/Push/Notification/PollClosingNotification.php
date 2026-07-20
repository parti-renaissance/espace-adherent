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
            '🚨 Plus qu’une heure !',
            'Dernière chance de participer au sondage.',
            NotificationScope::pollNonVoters($poll->getId()),
        );
    }
}
