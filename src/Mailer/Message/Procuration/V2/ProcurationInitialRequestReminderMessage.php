<?php

declare(strict_types=1);

namespace App\Mailer\Message\Procuration\V2;

use App\Entity\ProcurationV2\ProcurationRequest;
use App\Mailer\Message\Message;
use Ramsey\Uuid\Uuid;

final class ProcurationInitialRequestReminderMessage extends AbstractProcurationMessage
{
    protected const SENDER_NAME = 'Romane de Besoin d\'Europe';

    public static function create(ProcurationRequest $procurationRequest): Message
    {
        $message = new self(
            Uuid::uuid4(),
            $procurationRequest->email,
            null,
            'Souhaitez-vous toujours faire procuration ?'
        );

        return self::updateSenderInfo($message);
    }
}
