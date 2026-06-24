<?php

declare(strict_types=1);

namespace App\JeMengage\Push\Notification;

use App\Entity\Pronostic\Pronostic;
use App\Firebase\Notification\AbstractMulticastNotification;
use App\JeMengage\Push\NotificationScope;

class PronosticCreationNotification extends AbstractMulticastNotification
{
    public static function create(Pronostic $pronostic): self
    {
        $match = \sprintf('%s - %s', $pronostic->team1, $pronostic->team2);

        return new self(
            'Nouveau pronostic à tenter !',
            \sprintf('Pronostiquez le résultat de %s et tentez votre chance !', $match),
            NotificationScope::national(),
        );
    }
}
