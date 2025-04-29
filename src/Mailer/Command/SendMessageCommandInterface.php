<?php

namespace App\Mailer\Command;

use Ramsey\Uuid\UuidInterface;

interface SendMessageCommandInterface
{
    public function getUuid(): UuidInterface;

    public function isResend(): bool;
}
