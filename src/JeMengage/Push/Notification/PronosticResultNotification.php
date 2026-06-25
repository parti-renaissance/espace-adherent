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
            'Résultats du pronostic',
            \sprintf('Les résultats de « %s » sont disponibles. Avez-vous gagné ?', $pronostic->title),
            NotificationScope::pronosticParticipants($pronostic->getId()),
        );
    }
}
