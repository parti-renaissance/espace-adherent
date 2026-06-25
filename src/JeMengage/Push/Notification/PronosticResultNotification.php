<?php

declare(strict_types=1);

namespace App\JeMengage\Push\Notification;

use App\Entity\Pronostic\Pronostic;
use App\Firebase\Notification\AbstractMulticastNotification;
use App\JeMengage\Push\NotificationScope;

class PronosticResultNotification extends AbstractMulticastNotification
{
    public static function create(Pronostic $pronostic): self
    {
        return new self(
            '🏆 Tu perds, tu retentes !',
            'Découvre immédiatement si tu as réussi à donner une leçon de pronostic à Gabriel Attal.',
            NotificationScope::pronosticParticipants($pronostic->getId()),
        );
    }
}
