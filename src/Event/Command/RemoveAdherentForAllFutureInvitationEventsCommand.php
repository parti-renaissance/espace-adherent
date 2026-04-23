<?php

declare(strict_types=1);

namespace App\Event\Command;

use App\Messenger\Message\UuidDefaultAsyncMessage;
use Ramsey\Uuid\UuidInterface;

class RemoveAdherentForAllFutureInvitationEventsCommand extends UuidDefaultAsyncMessage
{
    public function __construct(
        UuidInterface $uuid,
        public readonly ?int $agoraId = null,
        public readonly ?int $committeeId = null,
    ) {
        parent::__construct($uuid);
    }
}
