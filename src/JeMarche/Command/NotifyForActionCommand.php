<?php

namespace App\JeMarche\Command;

use App\Messenger\Message\UuidDefaultAsyncMessage;
use Ramsey\Uuid\UuidInterface;

class NotifyForActionCommand extends UuidDefaultAsyncMessage
{
    public const string EVENT_CREATE = 'create';
    public const string EVENT_CANCEL = 'cancel';
    public const string EVENT_UPDATE = 'update';

    public const string EVENT_FIRST_NOTIFICATION = 'first_notification';
    public const string EVENT_SECOND_NOTIFICATION = 'second_notification';

    public function __construct(UuidInterface $uuid, public readonly string $event)
    {
        parent::__construct($uuid);
    }
}
