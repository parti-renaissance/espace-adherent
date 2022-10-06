<?php

namespace App\Mailer\Message\Renaissance;

use App\Entity\Renaissance\Adhesion\AdherentRequest;
use Ramsey\Uuid\Uuid;

class RenaissanceAdherentAccountActivationMessage extends AbstractRenaissanceMessage
{
    public static function create(AdherentRequest $adherentRequest, string $confirmationLink): self
    {
        return new self(
            Uuid::uuid4(),
            $adherentRequest->email,
            $adherentRequest->getFullName(),
            'Confirmez votre compte Renaissance',
            [],
            [
                'first_name' => self::escape($adherentRequest->firstName),
                'activation_link' => $confirmationLink,
            ],
        );
    }
}
