<?php

namespace AppBundle\Mailer\Message;

use AppBundle\Assessor\AssessorRequestCommand;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Intl\Intl;

final class AssessorRequestConfirmationMessage extends Message
{
    public static function createFromAssessorRequestCommand(AssessorRequestCommand $assessorRequest): self
    {
        $message = new self(
            Uuid::uuid4(),
            '769755',
            $assessorRequest->getEmailAddress(),
            null,
            '',
            [
                'firstname' => $assessorRequest->getFirstName(),
                'city_name_candidacy' => $assessorRequest->getAssessorCity(),
                'country_name_candidacy' => Intl::getRegionBundle()->getCountryName(
                    $assessorRequest->getAssessorCountry()
                ),
            ]
        );

        return $message;
    }
}
