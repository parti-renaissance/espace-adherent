<?php

namespace App\JeMarche\Notification;

use App\Entity\Event\CommitteeEvent;
use App\Firebase\Notification\AbstractMulticastNotification;

class CommitteeEventCreatedNotification extends AbstractMulticastNotification
{
    public static function create(array $tokens, CommitteeEvent $event): self
    {
        return new self(
            'Nouvel événement dans votre comité',
            sprintf('%s • %s • %s',
                $event->getName(),
                self::formatDate($event->getBeginAt(), 'EEEE d MMMM y à HH\'h\'mm'),
                $event->getInlineFormattedAddress()
            ),
            $tokens
        );
    }
}
