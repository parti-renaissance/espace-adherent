<?php

namespace AppBundle\Mailer\Message;

use AppBundle\Entity\ProcurationRequest;
use Ramsey\Uuid\Uuid;

final class ProcurationRequestRegistrationConfirmationMessage extends Message
{
    public static function create(ProcurationRequest $request): self
    {
        $message = new self(
            Uuid::uuid4(),
            '744709',
            $request->getEmailAddress(),
            null,
            'Vous souhaitez trouver un mandataire pour les élections européennes'
        );

        $message->setSenderName('La République En Marche !');

        return $message;
    }
}
