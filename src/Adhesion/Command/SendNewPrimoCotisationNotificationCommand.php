<?php

declare(strict_types=1);

namespace App\Adhesion\Command;

use App\Messenger\Message\AbstractUuidMessage;
use App\Notifier\AsyncNotificationInterface;
use Ramsey\Uuid\UuidInterface;

class SendNewPrimoCotisationNotificationCommand extends AbstractUuidMessage implements AsyncNotificationInterface
{
    public function __construct(UuidInterface $uuid, public readonly float $amount)
    {
        parent::__construct($uuid);
    }
}
