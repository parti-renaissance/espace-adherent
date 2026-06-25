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
            '⚽ Défiez les pronos de Gabriel Attal',
            \sprintf("Vous défiez ses pronos, il vous apprend à les respecter !\nGabriel Attal a fait son choix pour %s. Pouvez-vous faire mieux ?", $match),
            NotificationScope::national(),
        );
    }
}
