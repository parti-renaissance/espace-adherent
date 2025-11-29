<?php

declare(strict_types=1);

namespace App\JeMengage\Push\Notification;

use App\Action\ActionTypeEnum;
use App\Entity\Action\Action;
use App\Firebase\Notification\AbstractMulticastNotification;

class ActionBeginNotification extends AbstractMulticastNotification
{
    public static function create(Action $action, bool $firstNotification): self
    {
        return new self(
            static::createTitle($action, $firstNotification),
            static::createBody($action, $firstNotification),
        );
    }

    private static function createTitle(Action $action, bool $firstNotification): string
    {
        if ($firstNotification) {
            return \sprintf(
                'H-1 avant le %s à %s',
                ActionTypeEnum::LABELS[$action->type],
                $action->getCityName()
            );
        }

        return \sprintf(
            'C\'est parti pour le %s à %s',
            ActionTypeEnum::LABELS[$action->type],
            $action->getCityName()
        );
    }

    private static function createBody(Action $action, bool $firstNotification): string
    {
        if ($firstNotification) {
            return \sprintf(
                'Rendez vous au %s à %s. %s vous attend !',
                $action->getAddress(),
                $action->date->format('H\hi'),
                $action->getAuthor()?->getFirstName()
            );
        }

        return \sprintf(
            'Le %s commence ! Rendez vous au %s. Vous êtes sur place ? Déclarez votre présence !',
            ActionTypeEnum::LABELS[$action->type],
            $action->getAddress()
        );
    }
}
