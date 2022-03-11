<?php

namespace App\Mailer\Message\Assessor;

use App\Entity\AssessorRequest;
use App\Mailer\Message\Message;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Intl\Countries;

final class AssessorRequestAssociateMessage extends AbstractAssessorMessage
{
    public static function create(AssessorRequest $assessorRequest, string $officeName): Message
    {
        $message = new self(
            Uuid::uuid4(),
            $assessorRequest->getEmailAddress(),
            null,
            'Félicitations ! Vous avez été sélectionné(e) pour être assesseur(e)',
            [
                'firstname' => $assessorRequest->getFirstName(),
                'assessor_function' => $officeName,
                'polling_station_name' => $assessorRequest->getVotePlace()->getLabel(),
                'polling_station_number' => $assessorRequest->getVotePlace()->getCode(),
                'polling_station_city_name' => $assessorRequest->getVotePlace()->getCity(),
                'polling_station_country_name' => Countries::getName(
                    $assessorRequest->getVotePlace()->getCountry()
                ),
            ]
        );

        return self::updateSenderInfo($message);
    }
}
