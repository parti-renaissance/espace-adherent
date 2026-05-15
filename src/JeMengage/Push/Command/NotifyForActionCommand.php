<?php

declare(strict_types=1);

namespace App\JeMengage\Push\Command;

use App\Entity\Action\Action;
use Symfony\Component\Uid\Uuid;

class NotifyForActionCommand extends AbstractSendNotificationCommand
{
    public const string EVENT_CREATE = 'create';
    public const string EVENT_CANCEL = 'cancel';
    public const string EVENT_UPDATE = 'update';

    public const string EVENT_FIRST_NOTIFICATION = 'first_notification';
    public const string EVENT_SECOND_NOTIFICATION = 'second_notification';

    public function __construct(Uuid $uuid, public readonly string $event)
    {
        parent::__construct($uuid);
    }

    public function getClass(): string
    {
        return Action::class;
    }
}
