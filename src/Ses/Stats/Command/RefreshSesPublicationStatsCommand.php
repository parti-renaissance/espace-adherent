<?php

declare(strict_types=1);

namespace App\Ses\Stats\Command;

use App\Messenger\Message\AbstractUuidMessage;
use App\Messenger\Message\AsynchronousMessageInterface;
use Symfony\Component\Uid\Uuid;

class RefreshSesPublicationStatsCommand extends AbstractUuidMessage implements AsynchronousMessageInterface
{
    public function __construct(
        Uuid $uuid,
        public readonly bool $autoReschedule = true,
    ) {
        parent::__construct($uuid);
    }
}
