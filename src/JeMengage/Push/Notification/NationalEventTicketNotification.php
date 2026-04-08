<?php

declare(strict_types=1);

namespace App\JeMengage\Push\Notification;

use App\Entity\NationalEvent\NationalEvent;
use App\Firebase\Notification\AbstractMulticastNotification;
use App\JeMengage\Push\NotificationScope;

class NationalEventTicketNotification extends AbstractMulticastNotification
{
    public static function create(NationalEvent $event): self
    {
        return new self(
            '🎟️ Votre billet pour l\'événement',
            'Est directement accessible depuis votre Espace Militant.',
            NotificationScope::meeting($event->getId()),
        );
    }
}
