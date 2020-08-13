<?php

namespace App\Mailer\Message;

use App\Entity\Adherent;
use App\Entity\VotingPlatform\Designation\Designation;
use Ramsey\Uuid\Uuid;

class TerritorialCouncilCandidacyInvitationCreatedMessage extends Message
{
    public static function create(
        Adherent $invited,
        Adherent $candidate,
        Designation $designation,
        string $quality,
        string $invitationUrl
    ): self {
        return new self(
            Uuid::uuid4(),
            $invited->getEmailAddress(),
            $invited->getFullName(),
            sprintf('[Désignations] %s vous a invité(e) à candidater en binôme', $candidate->getFirstName()),
            [
                'invited_first_name' => $invited->getFirstName(),
                'candidate_first_name' => $candidate->getFirstName(),
                'candidate_last_name' => $candidate->getLastName(),
                'quality' => $quality,
                'candidacy_end_date' => self::dateToString($designation->getCandidacyEndDate()),
                'invitation_url' => $invitationUrl,
            ]
        );
    }
}
