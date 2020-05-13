<?php

namespace App\Mailer\Message;

use App\Entity\ProcurationProxy;
use Ramsey\Uuid\Uuid;

final class ProcurationProxyRegistrationConfirmationMessage extends Message
{
    public static function create(ProcurationProxy $procurationProxy): self
    {
        return new self(
            Uuid::uuid4(),
            $procurationProxy->getEmailAddress(),
            null,
            'Vous souhaitez être mandataire',
            ['election' => $procurationProxy->getElection()->getName()]
        );
    }
}
