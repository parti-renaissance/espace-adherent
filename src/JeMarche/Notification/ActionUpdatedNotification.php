<?php

namespace App\JeMarche\Notification;

use App\Action\ActionTypeEnum;
use App\Entity\Action\Action;
use App\Firebase\Notification\AbstractMulticastNotification;

class ActionUpdatedNotification extends AbstractMulticastNotification
{
    public static function create(Action $action, array $topics): self
    {
        return new self(
            static::createTitle($action),
            static::createBody($action),
            $topics
        );
    }

    private static function createTitle(Action $action): string
    {
        $typeLabel = sprintf('%s %s', ActionTypeEnum::EMOJIS[$action->type], ActionTypeEnum::LABELS[$action->type]);

        return sprintf(
            '%s le %d %s à %s',
            $typeLabel,
            $action->date->format('d'),
            static::formatDate($action->date, 'MMMM'),
            $action->getCityName()
        );
    }

    private static function createBody(Action $action): string
    {
        return sprintf(
            'Le %s du %s auquel vous êtes inscrit vient d\'être modifié.',
            mb_strtolower(ActionTypeEnum::LABELS[$action->type]),
            static::formatDate($action->date, 'EEEE d MMMM à HH\'h\'mm'),
        );
    }
}
