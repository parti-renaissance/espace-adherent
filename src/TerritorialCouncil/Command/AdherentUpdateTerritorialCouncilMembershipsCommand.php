<?php

namespace App\TerritorialCouncil\Command;

use App\Messenger\Message\AsynchronousMessageInterface;
use Ramsey\Uuid\UuidInterface;

class AdherentUpdateTerritorialCouncilMembershipsCommand implements AsynchronousMessageInterface
{
    private $uuid;
    private $eventDispatchingEnabled;

    public function __construct(UuidInterface $uuid, bool $eventDispatchingEnabled = true)
    {
        $this->uuid = $uuid;
        $this->eventDispatchingEnabled = $eventDispatchingEnabled;
    }

    public function getUuid(): UuidInterface
    {
        return $this->uuid;
    }

    public function isEventDispatchingEnabled(): bool
    {
        return $this->eventDispatchingEnabled;
    }
}
