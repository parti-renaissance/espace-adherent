<?php

namespace App\TerritorialCouncil\Command;

use App\Messenger\Message\AsynchronousMessageInterface;
use Ramsey\Uuid\UuidInterface;

class AdherentUpdateTerritorialCouncilMembershipsCommand implements AsynchronousMessageInterface
{
    private $uuid;

    public function __construct(UuidInterface $uuid)
    {
        $this->uuid = $uuid;
    }

    public function getUuid(): UuidInterface
    {
        return $this->uuid;
    }
}
