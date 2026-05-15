<?php

declare(strict_types=1);

namespace App\Mailer\Command;

use Symfony\Component\Uid\Uuid;

interface SendMessageCommandInterface
{
    public function getUuid(): Uuid;

    public function isResend(): bool;
}
