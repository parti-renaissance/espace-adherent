<?php

declare(strict_types=1);

namespace App\Adhesion\Command;

use App\Adhesion\AdherentRequestReminderTypeEnum;
use App\Messenger\Message\UuidDefaultAsyncMessage;
use Ramsey\Uuid\UuidInterface;

class SendAdherentRequestReminderCommand extends UuidDefaultAsyncMessage
{
    public function __construct(UuidInterface $uuid, public readonly AdherentRequestReminderTypeEnum $reminderType)
    {
        parent::__construct($uuid);
    }
}
