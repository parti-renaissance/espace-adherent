<?php

namespace App\Messenger\Message;

use Ramsey\Uuid\UuidInterface;

abstract class AbstractUuidAsynchronousMessage implements AsynchronousMessageInterface
{
    protected $uuid;

    public function __construct(UuidInterface $uuid)
    {
        $this->uuid = $uuid;
    }

    public function getUuid(): UuidInterface
    {
        return $this->uuid;
    }
}
