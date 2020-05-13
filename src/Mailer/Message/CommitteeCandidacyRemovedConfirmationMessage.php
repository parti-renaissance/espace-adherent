<?php

namespace App\Mailer\Message;

use App\Entity\Adherent;
use App\Entity\CommitteeElection;
use Ramsey\Uuid\Uuid;

final class CommitteeCandidacyRemovedConfirmationMessage extends Message
{
    public static function create(Adherent $candidate, CommitteeElection $election, string $committeeUrl): self
    {
        return new self(
            Uuid::uuid4(),
            $candidate->getEmailAddress(),
            $candidate->getFullName(),
            '[Désignations] Votre candidature a été annulée',
            static::getTemplateVars($candidate, $election, $committeeUrl),
        );
    }

    private static function getTemplateVars(
        Adherent $candidate,
        CommitteeElection $election,
        string $committeeUrl
    ): array {
        return [
            'first_name' => $candidate->getFirstName(),
            'committee_name' => self::escape($election->getCommittee()->getName()),
            'candidacy_end_date' => self::dateToString($election->getCandidacyPeriodEndDate()),
            'vote_start_date' => self::dateToString($election->getVoteStartDate()),
            'vote_end_date' => self::dateToString($election->getVoteEndDate()),
            'committee_url' => $committeeUrl,
        ];
    }

    private static function dateToString(\DateTimeInterface $date): string
    {
        return parent::formatDate($date, 'EEEE d MMMM y, HH\'h\'mm');
    }
}
