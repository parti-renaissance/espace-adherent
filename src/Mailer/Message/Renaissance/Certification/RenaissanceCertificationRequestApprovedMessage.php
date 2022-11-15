<?php

namespace App\Mailer\Message\Renaissance\Certification;

use App\Entity\CertificationRequest;
use App\Mailer\Message\Message;
use Ramsey\Uuid\Uuid;

final class RenaissanceCertificationRequestApprovedMessage extends Message
{
    public static function create(CertificationRequest $certificationRequest): self
    {
        $adherent = $certificationRequest->getAdherent();

        return new static(
            Uuid::uuid4(),
            $adherent->getEmailAddress(),
            $adherent->getFullName(),
            'Vous êtes certifié(e)',
            [
                'first_name' => $adherent->getFirstName(),
            ]
        );
    }
}
