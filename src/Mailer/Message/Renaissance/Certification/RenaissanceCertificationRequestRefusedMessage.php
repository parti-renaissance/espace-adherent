<?php

declare(strict_types=1);

namespace App\Mailer\Message\Renaissance\Certification;

use App\Entity\CertificationRequest;
use App\Mailer\Message\Renaissance\AbstractRenaissanceMessage;
use Ramsey\Uuid\Uuid;

final class RenaissanceCertificationRequestRefusedMessage extends AbstractRenaissanceMessage
{
    public static function create(
        CertificationRequest $certificationRequest,
        string $refusalReason,
        string $certificationRequestUrl,
    ): self {
        $adherent = $certificationRequest->getAdherent();

        return new static(
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
