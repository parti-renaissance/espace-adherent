<?php

namespace App\Event\Command;

use App\Messenger\Message\CronjobMessageInterface;
use App\Messenger\Message\UuidDefaultAsyncMessage;

class EventLiveBeginNotificationCommand extends UuidDefaultAsyncMessage implements CronjobMessageInterface
{
}
