<?php

namespace App\Mailer\Message;

use App\Assessor\AssessorRequestCommand;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Intl\Countries;

final class AssessorRequestConfirmationMessage extends Message
{
    public static function createFromAssessorRequestCommand(AssessorRequestCommand $assessorRequest): self
    {
        $message = new self(
            Uuid::uuid4(),
            $assessorRequest->getEmailAddress(),
            null,
            'Vous avez candidaté au poste d\'assesseur(e)',
            [
                'firstname' => $assessorRequest->getFirstName(),
                'city_name_candidacy' => (string) $assessorRequest->getAssessorCity(),
                'country_name_candidacy' => Countries::getName($assessorRequest->getAssessorCountry()),
            ]
        );

        return $message;
    }
}
