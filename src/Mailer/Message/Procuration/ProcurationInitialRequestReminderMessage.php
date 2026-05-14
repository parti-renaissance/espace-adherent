<?php

declare(strict_types=1);

namespace App\Mailer\Message\Procuration;

use App\Entity\Procuration\ProcurationRequest;
use App\Mailer\Message\Message;
use Symfony\Component\Uid\Uuid;

final class ProcurationInitialRequestReminderMessage extends AbstractProcurationMessage
{
    public static function create(ProcurationRequest $procurationRequest): Message
    {
        $message = new self(
            Uuid::v4(),
            $procurationRequest->email,
            null,
            'Souhaitez-vous toujours faire procuration ?'
        );

        return self::updateSenderInfo($message);
    }
}
