<?php

namespace App\Mailer\Message;

use App\Entity\Adherent;
use App\Entity\Committee;
use App\Entity\VotingPlatform\Designation\Designation;
use Ramsey\Uuid\Uuid;

class CommitteeElectionCandidacyPeriodIsOverMessage extends Message
{
    public static function create(
        Adherent $adherent,
        Committee $committee,
        Designation $designation,
        string $committeeUrl
    ): self {
        return new self(
            Uuid::uuid4(),
            $adherent->getEmailAddress(),
            $adherent->getFullName(),
            '[Désignations] Les candidatures sont closes',
            [
                'first_name' => $adherent->getFirstName(),
                'committee_name' => static::escape($committee->getName()),
                'vote_start_date' => static::formatDate($designation->getVoteStartDate(), 'EEEE d MMMM y, HH\'h\'mm'),
                'vote_end_date' => static::formatDate($designation->getVoteEndDate(), 'EEEE d MMMM y, HH\'h\'mm'),
                'committee_url' => $committeeUrl,
            ],
        );
    }
}
