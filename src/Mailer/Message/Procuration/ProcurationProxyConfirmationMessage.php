<?php

declare(strict_types=1);

namespace App\Mailer\Message\Procuration;

use App\Entity\Procuration\Proxy;
use App\Mailer\Message\Message;
use Symfony\Component\Uid\Uuid;

final class ProcurationProxyConfirmationMessage extends AbstractProcurationMessage
{
    public static function create(Proxy $proxy): Message
    {
        $message = new self(
            Uuid::v4(),
            $proxy->email,
            null,
            '[Procuration] Demande prise en compte'
        );

        return self::updateSenderInfo($message);
    }
}
