<?php

declare(strict_types=1);

namespace App\Mailer\Message\Procuration;

use App\Entity\Procuration\Request;
use App\Mailer\Message\Message;
use Symfony\Component\Uid\Uuid;

final class ProcurationRequestConfirmationMessage extends AbstractProcurationMessage
{
    public static function create(Request $request): Message
    {
        $message = new self(
            Uuid::v4(),
            $request->email,
            null,
            '[Procuration] Demande prise en compte'
        );

        return self::updateSenderInfo($message);
    }
}
