<?php

declare(strict_types=1);

namespace App\Mailer\Message\Renaissance\Certification;

use App\Entity\CertificationRequest;
use App\Mailer\Message\Renaissance\AbstractRenaissanceMessage;
use Ramsey\Uuid\Uuid;

final class RenaissanceCertificationRequestPendingMessage extends AbstractRenaissanceMessage
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
