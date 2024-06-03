<?php

namespace App\JeMarche\Notification;

use App\Entity\Jecoute\Riposte;
use App\Firebase\Notification\AbstractTopicNotification;

class RiposteCreatedNotification extends AbstractTopicNotification
{
    public static function create(Riposte $riposte, string $topic): self
    {
        $notification = new self(
            $riposte->getTitle(),
            $riposte->getBody(),
            $topic
        );

        $notification->setDeepLinkFromObject($riposte);

        return $notification;
    }
}
