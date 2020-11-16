<?php

namespace App\Mailer\Message;

use App\Entity\Adherent;
use App\Entity\VotingPlatform\Designation\Designation;
use Ramsey\Uuid\Uuid;

final class VotingPlatformCandidacyInvitationRemovedMessage extends AbstractVotingPlatformCandidacyInvitationMessage
{
    public static function create(Adherent $invited, Adherent $candidate, Designation $designation, string $url): self
    {
        $isCommittee = $designation->isCommitteeType();

        return new self(
            Uuid::uuid4(),
            $invited->getEmailAddress(),
            $invited->getFullName(),
            sprintf('%s %s a annulé son invitation', self::getMailSubjectPrefix($isCommittee), $candidate->getFirstName()),
            [
                'is_committee' => $isCommittee,
                'invited_first_name' => $invited->getFirstName(),
                'candidate_first_name' => $candidate->getFirstName(),
                'candidate_last_name' => $candidate->getLastName(),
                'candidacy_end_date' => self::dateToString($designation->getCandidacyEndDate()),
                'action_url' => $url,
            ]
        );
    }
}
