<?php

namespace App\Mailer\Message;

use App\Entity\Adherent;
use App\Entity\VotingPlatform\Designation\Designation;
use Ramsey\Uuid\Uuid;

final class VotingPlatformCandidacyInvitationDeclinedMessage extends AbstractVotingPlatformCandidacyInvitationMessage
{
    public static function create(
        Adherent $invited,
        Adherent $candidate,
        Designation $designation,
        string $invitationFormUrl
    ): self {
        $isCommittee = $designation->isCommitteeType();

        return new self(
            Uuid::uuid4(),
            $candidate->getEmailAddress(),
            $candidate->getFullName(),
            sprintf('%s %s a décliné votre invitation', self::getMailSubjectPrefix($isCommittee), $invited->getFirstName()),
            [
                'is_committee' => $isCommittee,
                'candidate_first_name' => $candidate->getFirstName(),
                'invited_first_name' => $invited->getFirstName(),
                'invited_last_name' => $invited->getLastName(),
                'candidacy_end_date' => self::dateToString($designation->getCandidacyEndDate()),
                'invitation_form_url' => $invitationFormUrl,
            ]
        );
    }
}
