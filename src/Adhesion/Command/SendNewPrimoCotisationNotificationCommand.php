<?php

declare(strict_types=1);

namespace App\Adhesion\Command;

use App\Messenger\Message\AbstractUuidMessage;
use App\Notifier\AsyncNotificationInterface;
use Symfony\Component\Uid\Uuid;

class SendNewPrimoCotisationNotificationCommand extends AbstractUuidMessage implements AsyncNotificationInterface
{
    public function __construct(Uuid $uuid, public readonly float $amount)
    {
        parent::__construct($uuid);
    }
}
