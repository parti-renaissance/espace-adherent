<?php

namespace App\SendInBlue\Command;

use App\Messenger\Message\AbstractUuidAsynchronousMessage;
use App\SendInBlue\SynchronizeMessageInterface;
use Ramsey\Uuid\UuidInterface;

class AdherentSynchronisationCommand extends AbstractUuidAsynchronousMessage implements SynchronizeMessageInterface
{
    private string $identifier;

    public function __construct(UuidInterface $uuid, string $identifier)
    {
        parent::__construct($uuid);

        $this->identifier = $identifier;
    }

    public function getIdentifier(): string
    {
        return $this->identifier;
    }
}
