<?php

declare(strict_types=1);

namespace App\Event\Command;

use App\Messenger\Message\CronjobMessageInterface;
use App\Messenger\Message\UuidDefaultAsyncMessage;

class EventLiveBeginEmailNotificationCommand extends UuidDefaultAsyncMessage implements CronjobMessageInterface
{
}
