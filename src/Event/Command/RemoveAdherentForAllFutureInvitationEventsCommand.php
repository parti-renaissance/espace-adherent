<?php

declare(strict_types=1);

namespace App\Event\Command;

use App\Messenger\Message\UuidDefaultAsyncMessage;
use Symfony\Component\Uid\Uuid;

class RemoveAdherentForAllFutureInvitationEventsCommand extends UuidDefaultAsyncMessage
{
    public function __construct(
        Uuid $uuid,
        public readonly ?int $agoraId = null,
        public readonly ?int $committeeId = null,
    ) {
        parent::__construct($uuid);
    }
}
