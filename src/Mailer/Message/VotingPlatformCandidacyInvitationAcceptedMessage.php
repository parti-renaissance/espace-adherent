<?php

namespace App\Mailer\Message;

use App\Entity\Adherent;
use App\Entity\VotingPlatform\Designation\Designation;
use Ramsey\Uuid\Uuid;

final class VotingPlatformCandidacyInvitationAcceptedMessage extends AbstractVotingPlatformCandidacyInvitationMessage
{
    public static function create(
        Adherent $invited,
        Adherent $candidate,
        Designation $designation,
        string $candidaciesListUrl,
        array $params = []
    ): self {
        $isCommittee = $designation->isCommitteeType();

        $message = new self(
            Uuid::uuid4(),
            $candidate->getEmailAddress(),
            $candidate->getFullName(),
            sprintf('%s Félicitations, vous êtes candidat(e) en binôme !', self::getMailSubjectPrefix($isCommittee)),
            array_merge([
                'is_committee' => $isCommittee,
                'candidacy_end_date' => self::dateToString($designation->getCandidacyEndDate()),
                'vote_start_date' => self::dateToString($designation->getVoteStartDate()),
                'vote_end_date' => self::dateToString($designation->getVoteEndDate()),
                'candidacies_list_url' => $candidaciesListUrl,
            ], $params),
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
