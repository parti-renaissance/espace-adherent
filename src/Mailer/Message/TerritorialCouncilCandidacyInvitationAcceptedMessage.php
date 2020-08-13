<?php

namespace App\Mailer\Message;

use App\Entity\Adherent;
use App\Entity\VotingPlatform\Designation\Designation;
use Ramsey\Uuid\Uuid;

class TerritorialCouncilCandidacyInvitationAcceptedMessage extends Message
{
    public static function create(
        Adherent $invited,
        Adherent $candidate,
        Designation $designation,
        string $candidaciesListUrl
    ): self {
        $message = new self(
            Uuid::uuid4(),
            $candidate->getEmailAddress(),
            $candidate->getFullName(),
            '[Désignations] Félicitations, vous êtes candidat(e) en binôme !',
            [
                'candidacy_end_date' => self::dateToString($designation->getCandidacyEndDate()),
                'vote_start_date' => self::dateToString($designation->getVoteStartDate()),
                'result_end_date' => self::dateToString($designation->getResultEndDate()),
                'candidacies_list_url' => $candidaciesListUrl,
            ],
            [
                'candidate_first_name' => $candidate->getFirstName(),
                'binome_first_name' => $invited->getFirstName(),
                'binome_last_name' => $invited->getLastName(),
            ]
        );

        $message->addRecipient(
            $invited->getEmailAddress(),
            $invited->getFullName(),
            [
                'candidate_first_name' => $invited->getFirstName(),
                'binome_first_name' => $candidate->getFirstName(),
                'binome_last_name' => $candidate->getLastName(),
            ]
        );

        return $message;
    }
}
