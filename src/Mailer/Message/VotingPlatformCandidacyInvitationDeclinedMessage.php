<?php

declare(strict_types=1);

namespace App\Mailer\Message;

use App\Entity\Adherent;
use App\Entity\VotingPlatform\Designation\Designation;
use Ramsey\Uuid\Uuid;

final class VotingPlatformCandidacyInvitationDeclinedMessage extends AbstractVotingPlatformMessage
{
    public static function create(
        Adherent $invited,
        Adherent $candidate,
        Designation $designation,
        string $invitationFormUrl,
    ): self {
        $emailTitle = self::getMailSubjectPrefix($designation);

        return new self(
            Uuid::uuid4(),
            $candidate->getEmailAddress(),
            $candidate->getFullName(),
            \sprintf('[%s] %s a décliné votre invitation', $emailTitle, $invited->getFirstName()),
            [
                'email_title' => $emailTitle,
                'election_type' => $designation->getType(),
                'candidate_first_name' => $candidate->getFirstName(),
                'invited_first_name' => $invited->getFirstName(),
                'invited_last_name' => $invited->getLastName(),
                'candidacy_end_date' => self::dateToString($designation->getCandidacyEndDate()),
                'page_url' => $invitationFormUrl,
            ]
        );
    }
}
