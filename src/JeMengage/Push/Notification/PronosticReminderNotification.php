<?php

declare(strict_types=1);

namespace App\JeMengage\Push\Notification;

use App\Entity\Pronostic\Pronostic;
use App\Firebase\Notification\AbstractMulticastNotification;
use App\JeMengage\Push\NotificationScope;
use App\Pronostic\PronosticReminderTypeEnum;

class PronosticReminderNotification extends AbstractMulticastNotification
{
    public static function create(Pronostic $pronostic, PronosticReminderTypeEnum $type): self
    {
        $match = \sprintf('%s - %s', $pronostic->team1, $pronostic->team2);

        [$title, $body] = match ($type) {
            PronosticReminderTypeEnum::H_MINUS_1 => [
                'Dernière heure pour pronostiquer !',
                \sprintf('Le match %s commence dans 1h. Donnez vite votre pronostic !', $match),
            ],
            default => [
                'Plus qu’un jour pour pronostiquer !',
                \sprintf('Le match %s commence demain. Donnez votre pronostic avant le coup d’envoi !', $match),
            ],
        };

        return new self($title, $body, NotificationScope::national());
    }
}
