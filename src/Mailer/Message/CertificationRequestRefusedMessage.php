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
            [],
            static::getRecipientVars(
                $adherent->getFirstName(),
                $refusalReason,
                $certificationRequestUrl
            )
        );
    }

    private static function getRecipientVars(
        string $firstName,
        string $refusalReason,
        string $certificationRequestUrl
    ): array {
        return [
            'first_name' => self::escape($firstName),
            'refusal_reason' => $refusalReason,
            'certification_request_url' => $certificationRequestUrl,
        ];
    }
}
