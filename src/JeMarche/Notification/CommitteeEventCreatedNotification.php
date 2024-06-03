<?php

namespace App\JeMarche\Notification;

use App\Entity\Event\CommitteeEvent;
use App\Firebase\Notification\AbstractMulticastNotification;

class CommitteeEventCreatedNotification extends AbstractMulticastNotification
{
    public static function create(array $tokens, CommitteeEvent $event): self
    {
        $notification = new self(
            sprintf('Nouvel événement dans votre comité %s', $event->getCommittee()->getName()),
            sprintf('%s • %s • %s',
                $event->getName(),
                self::formatDate($event->getBeginAt(), 'EEEE d MMMM y à HH\'h\'mm'),
                $event->getInlineFormattedAddress()
            ),
            $tokens
        );

        $notification->setDeepLinkFromObject($event);

        return $notification;
    }
}
