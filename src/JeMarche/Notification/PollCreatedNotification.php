<?php

namespace App\JeMarche\Notification;

use App\Entity\Poll\Poll;
use App\Firebase\Notification\AbstractTopicNotification;

class PollCreatedNotification extends AbstractTopicNotification
{
    public static function create(Poll $poll, string $topic): self
    {
        return new self(
            $poll->getQuestion(),
            'Cliquez pour répondre à cette question du jour.',
            $topic
        );
    }
}
