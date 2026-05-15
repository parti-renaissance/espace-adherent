<?php

declare(strict_types=1);

namespace App\Messenger\Message;

use Symfony\Component\Uid\Uuid;

abstract class AbstractUuidMessage
{
    public function __construct(private readonly Uuid $uuid)
    {
    }

    public function getUuid(): Uuid
    {
        return $this->uuid;
    }
}
