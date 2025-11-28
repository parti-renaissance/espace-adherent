<?php

declare(strict_types=1);

namespace App\Mailer\Message\Procuration\V2;

use App\Entity\ProcurationV2\ProcurationRequest;
use App\Mailer\Message\Message;
use Ramsey\Uuid\Uuid;

final class ProcurationInitialProxyReminderMessage extends AbstractProcurationMessage
{
    protected const SENDER_NAME = 'Elise de Besoin d\'Europe';

    public static function create(ProcurationRequest $procurationRequest): Message
    {
        $message = new self(
            Uuid::uuid4(),
            $procurationRequest->email,
            null,
            'Êtes-vous toujours disponible le 9 juin ?'
        );

        return self::updateSenderInfo($message);
    }
}
