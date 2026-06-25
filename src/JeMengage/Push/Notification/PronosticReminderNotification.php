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
            PronosticReminderTypeEnum::H_MINUS_5_MIN => [
                '🔴 Plus que 5 minutes pour faire tes pronos !',
                'Les joueurs sont dans le couloir. Plus que 5 minutes pour enregistrer ton pronostic. Vite !',
            ],
            PronosticReminderTypeEnum::H_MINUS_1 => [
                '🚨 Rien ne résiste à un bon prono !',
                \sprintf('L’échauffement est terminé. Ne laisse pas Gabriel Attal gagner d’avance sur %s. Valide ton score !', $match),
            ],
            default => [
                '⏳ Il a déjà fait son choix...',
                \sprintf('Gabriel a verrouillé son score pour %s demain. À ton tour d’entrer sur le terrain.', $match),
            ],
        };

        return new self($title, $body, NotificationScope::pronosticNonParticipants($pronostic->getId()));
    }
}
