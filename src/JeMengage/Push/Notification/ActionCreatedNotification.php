<?php

declare(strict_types=1);

namespace App\JeMengage\Push\Notification;

use App\Action\ActionTypeEnum;
use App\Entity\Action\Action;
use App\Firebase\Notification\AbstractMulticastNotification;

class ActionCreatedNotification extends AbstractMulticastNotification
{
    public static function create(Action $action): self
    {
        return new self(
            static::createTitle($action),
            static::createBody($action),
        );
    }

    private static function createTitle(Action $action): string
    {
        $typeLabel = \sprintf('%s %s', ActionTypeEnum::EMOJIS[$action->type], ucfirst(ActionTypeEnum::LABELS[$action->type]));

        return \sprintf(
            '%s le %d %s à %s',
            $typeLabel,
            $action->date->format('d'),
            static::formatDate($action->date, 'MMMM'),
            $action->getCityName()
        );
    }

    private static function createBody(Action $action): string
    {
        return \sprintf(
            '%s vient de créer une nouvelle action de %s le %s à %s%s.',
            $action->getAuthor()?->getFirstName(),
            ActionTypeEnum::LABELS[$action->type],
            static::formatDate($action->date, 'EEEE d MMMM à HH\'h\'mm'),
            $action->getCityName(),
            $action->getPostalCode() ? \sprintf(' (%s)', $action->getPostalCode()) : ''
        );
    }
}
