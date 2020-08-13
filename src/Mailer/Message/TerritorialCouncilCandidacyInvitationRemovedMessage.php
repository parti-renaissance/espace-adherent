<?php

namespace App\Mailer\Message;

use App\Entity\Adherent;
use App\Entity\VotingPlatform\Designation\Designation;
use Ramsey\Uuid\Uuid;

class TerritorialCouncilCandidacyInvitationRemovedMessage extends Message
{
    public static function create(
        Adherent $invited,
        Adherent $candidate,
        Designation $designation,
        string $territorialCouncilUrl
    ): self {
        return new self(
            Uuid::uuid4(),
            $invited->getEmailAddress(),
            $invited->getFullName(),
            sprintf('[Désignations] %s a annulé son invitation', $candidate->getFirstName()),
            [
                'invited_first_name' => $invited->getFirstName(),
                'candidate_first_name' => $candidate->getFirstName(),
                'candidate_last_name' => $candidate->getLastName(),
                'candidacy_end_date' => self::dateToString($designation->getCandidacyEndDate()),
                'territorial_council_url' => $territorialCouncilUrl,
            ]
        );
    }
}
