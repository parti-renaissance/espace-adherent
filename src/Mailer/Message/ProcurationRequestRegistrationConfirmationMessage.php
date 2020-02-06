<?php

namespace AppBundle\Mailer\Message;

use AppBundle\Entity\ProcurationRequest;
use Ramsey\Uuid\Uuid;

final class ProcurationRequestRegistrationConfirmationMessage extends Message
{
    public static function create(ProcurationRequest $request): self
    {
        return new self(
            Uuid::uuid4(),
            $request->getEmailAddress(),
            null,
            'Vous souhaitez trouver un mandataire pour les prochaines élections',
            ['election' => $request->getElection()->getName()]
        );
    }
}
