<?php

declare(strict_types=1);

namespace App\JeMengage\Push\Command;

use App\Entity\Event\Event;
use Symfony\Component\Uid\Uuid;

class NotifyEventRegistrantsCommand extends AbstractSendNotificationCommand
{
    public const string EVENT_UPDATE = 'update';
    public const string EVENT_CANCEL = 'cancel';

    public function __construct(Uuid $uuid, public readonly string $event)
    {
        parent::__construct($uuid);
    }

    public function getClass(): string
    {
        return Event::class;
    }
}
