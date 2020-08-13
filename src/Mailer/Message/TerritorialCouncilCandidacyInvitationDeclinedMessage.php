<?php

namespace App\Mailer\Message;

use App\Entity\Adherent;
use App\Entity\VotingPlatform\Designation\Designation;
use Ramsey\Uuid\Uuid;

class TerritorialCouncilCandidacyInvitationDeclinedMessage extends Message
{
    public static function create(
        Adherent $invited,
        Adherent $candidate,
        Designation $designation,
        string $invitationFormUrl
    ): self {
        return new self(
            Uuid::uuid4(),
            $invited->getEmailAddress(),
            $invited->getFullName(),
            sprintf('[Désignations] %s a décliné votre invitation', $invited->getFirstName()),
            [
                'candidate_first_name' => $candidate->getFirstName(),
                'invited_first_name' => $invited->getFirstName(),
                'invited_last_name' => $invited->getLastName(),
                'candidacy_end_date' => self::dateToString($designation->getCandidacyEndDate()),
                'invitation_form_url' => $invitationFormUrl,
            ]
        );
    }
}
