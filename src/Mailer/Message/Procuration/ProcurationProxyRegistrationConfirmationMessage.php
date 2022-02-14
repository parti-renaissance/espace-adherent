<?php

namespace App\Mailer\Message\Procuration;

use App\Entity\ProcurationProxy;
use App\Mailer\Message\Message;
use Ramsey\Uuid\Uuid;

final class ProcurationProxyRegistrationConfirmationMessage extends AbstractProcurationMessage
{
    public static function create(ProcurationProxy $procurationProxy): Message
    {
        $message = new self(
            Uuid::uuid4(),
            $procurationProxy->getEmailAddress(),
            null,
            'Vous souhaitez être mandataire',
            ['election' => $procurationProxy->getElection()->getName()]
        );

        return self::updateSenderInfo($message);
    }
}
