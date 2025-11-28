<?php

declare(strict_types=1);

namespace App\Messenger\Message;

use Ramsey\Uuid\UuidInterface;

abstract class AbstractUuidMessage
{
    public function __construct(private readonly UuidInterface $uuid)
    {
    }

    public function getUuid(): UuidInterface
    {
        return $this->uuid;
    }
}
