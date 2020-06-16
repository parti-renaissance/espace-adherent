<?php

namespace App\Mailer\Message;

use App\Entity\CertificationRequest;
use Ramsey\Uuid\Uuid;

final class CertificationRequestBlockedMessage extends Message
{
    public static function create(CertificationRequest $certificationRequest): self
    {
        $adherent = $certificationRequest->getAdherent();

        return new self(
            Uuid::uuid4(),
            $adherent->getEmailAddress(),
            $adherent->getFullName(),
            'Votre profil ne peut pas être certifié',
            ['first_name' => $adherent->getFirstName()]
        );
    }
}
