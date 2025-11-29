<?php

declare(strict_types=1);

namespace App\Mailer\Message\Procuration\V2;

use App\Entity\ProcurationV2\Proxy;
use App\Mailer\Message\Message;
use Ramsey\Uuid\Uuid;

final class ProcurationProxyConfirmationMessage extends AbstractProcurationMessage
{
    public static function create(Proxy $proxy): Message
    {
        $message = new self(
            Uuid::uuid4(),
            $proxy->email,
            null,
            '[Procuration] Demande prise en compte'
        );

        return self::updateSenderInfo($message);
    }
}
