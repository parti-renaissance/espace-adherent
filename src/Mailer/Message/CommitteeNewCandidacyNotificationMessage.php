<?php

namespace AppBundle\Mailer\Message;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\CommitteeElection;
use AppBundle\ValueObject\Genders;
use Ramsey\Uuid\Uuid;

final class CommitteeNewCandidacyNotificationMessage extends Message
{
    public static function create(
        Adherent $supervisor,
        Adherent $candidate,
        CommitteeElection $election,
        string $committeeUrl
    ): self {
        return new self(
            Uuid::uuid4(),
            $supervisor->getEmailAddress(),
            $supervisor->getFullName(),
            '[Désignations] Une nouvelle candidature a été déposée',
            static::getTemplateVars($supervisor, $candidate, $election, $committeeUrl),
        );
    }

    private static function getTemplateVars(
        Adherent $supervisor,
        Adherent $candidate,
        CommitteeElection $election,
        string $committeeUrl
    ): array {
        $civility = '';

        if (Genders::MALE === $candidate->getGender()) {
            $civility = 'M.';
        } elseif (Genders::FEMALE === $candidate->getGender()) {
            $civility = 'Mme.';
        }

        return [
            'supervisor_first_name' => $supervisor->getFirstName(),
            'candidate_civility' => $civility,
            'candidate_first_name' => $candidate->getFirstName(),
            'candidate_last_name' => $candidate->getLastName(),
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
