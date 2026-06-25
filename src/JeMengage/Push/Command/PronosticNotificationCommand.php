<?php

declare(strict_types=1);

namespace App\JeMengage\Push\Command;

use App\Entity\Pronostic\Pronostic;
use App\Pronostic\PronosticReminderTypeEnum;
use Symfony\Component\Uid\Uuid;

class PronosticNotificationCommand extends AbstractSendNotificationCommand
{
    public function __construct(Uuid $uuid, public readonly PronosticReminderTypeEnum $type)
    {
        parent::__construct($uuid);
    }

    public function getClass(): string
    {
        return Pronostic::class;
    }
}
