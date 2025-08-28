<?php

namespace App\JeMengage\Push\Notification;

use App\Entity\Event\EventRegistration;
use App\Firebase\Notification\AbstractMulticastNotification;

class EventReferrerNotification extends AbstractMulticastNotification
{
    public static function create(EventRegistration $eventRegistration): self
    {
        return new self(
            \sprintf(
                '%s %s s\'est inscrit à %s depuis le lien que vous avez partagé.',
                $eventRegistration->getFirstName(),
                $eventRegistration->getLastName(),
                $eventRegistration->getEvent()->getName()
            ),
            'En partageant des liens d\'événements, vous participez activement au dynamisme de notre parti, merci !',
        );
    }
}
