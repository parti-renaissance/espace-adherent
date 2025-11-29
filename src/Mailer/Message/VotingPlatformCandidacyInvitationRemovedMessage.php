<?php

declare(strict_types=1);

namespace App\Mailer\Message;

use App\Entity\Adherent;
use App\Entity\VotingPlatform\Designation\Designation;
use Ramsey\Uuid\Uuid;

final class VotingPlatformCandidacyInvitationRemovedMessage extends AbstractVotingPlatformMessage
{
    public static function create(Adherent $invited, Adherent $candidate, Designation $designation, string $url): self
    {
        $emailTitle = self::getMailSubjectPrefix($designation);

        return new self(
            Uuid::uuid4(),
            $invited->getEmailAddress(),
            $invited->getFullName(),
            \sprintf('[%s] %s a annulé son invitation', $emailTitle, $candidate->getFirstName()),
            [
                'email_title' => $emailTitle,
                'election_type' => $designation->getType(),
                'invited_first_name' => $invited->getFirstName(),
                'candidate_first_name' => $candidate->getFirstName(),
                'candidate_last_name' => $candidate->getLastName(),
                'candidacy_end_date' => self::dateToString($designation->getCandidacyEndDate()),
                'page_url' => $url,
            ]
        );
    }
}
