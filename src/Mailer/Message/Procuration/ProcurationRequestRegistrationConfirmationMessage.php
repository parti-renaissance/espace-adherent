<?php

namespace App\Mailer\Message\Procuration;

use App\Entity\ProcurationRequest;
use App\Mailer\Message\Message;
use Ramsey\Uuid\Uuid;

final class ProcurationRequestRegistrationConfirmationMessage extends AbstractProcurationMessage
{
    public static function create(ProcurationRequest $request): Message
    {
        $message = new self(
            Uuid::uuid4(),
            $request->getEmailAddress(),
            null,
            'Vous souhaitez trouver un mandataire pour les prochaines élections',
            ['election' => $request->getElection()->getName()]
        );

        return self::updateSenderInfo($message);
    }
}
