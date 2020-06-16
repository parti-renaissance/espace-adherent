<?php

namespace App\Mailer\Message;

use App\Entity\CertificationRequest;
use Ramsey\Uuid\Uuid;

final class CertificationRequestRefusedMessage extends Message
{
    public static function create(
        CertificationRequest $certificationRequest,
        string $refusalReason,
        string $certificationRequestUrl
    ): self {
        $adherent = $certificationRequest->getAdherent();

        return new self(
            Uuid::uuid4(),
            $adherent->getEmailAddress(),
            $adherent->getFullName(),
            'Votre demande n\'a pas abouti',
            [
                'first_name' => $adherent->getFirstName(),
                'refusal_reason' => $refusalReason,
                'certification_request_url' => $certificationRequestUrl,
            ]
        );
    }
}
