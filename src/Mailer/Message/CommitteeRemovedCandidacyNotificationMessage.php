<?php

namespace App\Mailer\Message;

use App\Entity\Adherent;
use App\Entity\CommitteeElection;
use App\Entity\VotingPlatform\Designation\CandidacyInterface;
use Ramsey\Uuid\Uuid;

final class CommitteeRemovedCandidacyNotificationMessage extends Message
{
    public static function create(
        CandidacyInterface $committeeCandidacy,
        CommitteeElection $election,
        Adherent $supervisor,
        Adherent $candidate,
        string $committeeUrl
    ): self {
        return new self(
            Uuid::uuid4(),
            $supervisor->getEmailAddress(),
            $supervisor->getFullName(),
            '[Désignations] Une candidature a été retirée',
            static::getTemplateVars($committeeCandidacy, $election, $supervisor, $candidate, $committeeUrl),
        );
    }

    private static function getTemplateVars(
        CandidacyInterface $committeeCandidacy,
        CommitteeElection $election,
        Adherent $supervisor,
        Adherent $candidate,
        string $committeeUrl
    ): array {
        return [
            'supervisor_first_name' => $supervisor->getFirstName(),
            'candidate_civility' => $committeeCandidacy->getCivility(),
            'candidate_first_name' => $candidate->getFirstName(),
            'candidate_last_name' => $candidate->getLastName(),
            'vote_start_date' => self::dateToString($election->getVoteStartDate()),
            'vote_end_date' => self::dateToString($election->getVoteEndDate()),
            'committee_url' => $committeeUrl,
        ];
    }

    private static function dateToString(?\DateTimeInterface $date): string
    {
        return $date ? parent::formatDate($date, 'EEEE d MMMM y, HH\'h\'mm') : '';
    }
}
