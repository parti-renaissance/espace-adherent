<?php

declare(strict_types=1);

namespace App\Adhesion\Command;

use App\Adhesion\AdherentRequestReminderTypeEnum;
use App\Messenger\Message\UuidDefaultAsyncMessage;
use Symfony\Component\Uid\Uuid;

class SendAdherentRequestReminderCommand extends UuidDefaultAsyncMessage
{
    public function __construct(Uuid $uuid, public readonly AdherentRequestReminderTypeEnum $reminderType)
    {
        parent::__construct($uuid);
    }
}
