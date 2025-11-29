<?php

declare(strict_types=1);

namespace App\Agora\Command;

use App\Messenger\Message\UuidDefaultAsyncMessage;
use Ramsey\Uuid\UuidInterface;

class InviteAdherentForAllFuturAgoraEventCommand extends UuidDefaultAsyncMessage
{
    public function __construct(UuidInterface $uuid, public readonly int $agoraId)
    {
        parent::__construct($uuid);
    }
}
