<?php

namespace App\Mailer\Message;

use App\Entity\AssessorRequest;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Intl\Intl;

final class AssessorRequestAssociateMessage extends Message
{
    public static function create(AssessorRequest $assessorRequest, string $officeName): self
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
                'polling_station_country_name' => Intl::getRegionBundle()->getCountryName(
                    $assessorRequest->getVotePlace()->getCountry()
                ),
            ]
        );

        return $message;
    }
}
