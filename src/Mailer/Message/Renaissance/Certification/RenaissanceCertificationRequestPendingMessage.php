<?php

namespace App\Mailer\Message\Renaissance\Certification;

use App\Entity\CertificationRequest;
use App\Mailer\Message\Message;
use Ramsey\Uuid\Uuid;

final class RenaissanceCertificationRequestPendingMessage extends Message
{
    public static function create(CertificationRequest $certificationRequest): self
    {
        $adherent = $certificationRequest->getAdherent();

        return new static(
            Uuid::uuid4(),
            $adherent->getEmailAddress(),
            $adherent->getFullName(),
            'Votre demande a bien été reçue',
            [
                'first_name' => $adherent->getFirstName(),
            ]
        );
    }
}
