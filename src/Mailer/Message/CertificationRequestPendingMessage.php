<?php

namespace App\Mailer\Message;

use App\Entity\CertificationRequest;
use Ramsey\Uuid\Uuid;

final class CertificationRequestPendingMessage extends Message
{
    public static function create(CertificationRequest $certificationRequest): self
    {
        $adherent = $certificationRequest->getAdherent();

        return new self(
            Uuid::uuid4(),
            $adherent->getEmailAddress(),
            $adherent->getFullName(),
            'Votre demande a bien été reçue',
            ['first_name' => $adherent->getFirstName()]
        );
    }
}
