<?php

namespace App\Mailer\Message;

use App\Entity\Adherent;
use App\Entity\CommitteeElection;
use Ramsey\Uuid\Uuid;

final class CommitteeCandidacyCreatedConfirmationMessage extends Message
{
    public static function create(Adherent $candidate, CommitteeElection $election, string $cancelCandidacyUrl): self
    {
        return new self(
            Uuid::uuid4(),
            $candidate->getEmailAddress(),
            $candidate->getFullName(),
            '[Désignations] Vous êtes maintenant candidat(e) !',
            static::getTemplateVars($candidate, $election, $cancelCandidacyUrl),
        );
    }

    private static function getTemplateVars(
        Adherent $candidate,
        CommitteeElection $election,
        string $cancelCandidacyUrl
    ): array {
        return [
            'first_name' => $candidate->getFirstName(),
            'committee_name' => self::escape($election->getCommittee()->getName()),
            'candidacy_end_date' => self::dateToString($election->getCandidacyPeriodEndDate()),
            'vote_start_date' => self::dateToString($election->getVoteStartDate()),
            'vote_end_date' => self::dateToString($election->getVoteEndDate()),
            'cancel_candidacy_url' => $cancelCandidacyUrl,
        ];
    }

    private static function dateToString(\DateTimeInterface $date): string
    {
        return parent::formatDate($date, 'EEEE d MMMM y, HH\'h\'mm');
    }
}
