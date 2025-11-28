<?php

declare(strict_types=1);

namespace App\Mailer\Message;

use App\Entity\Adherent;
use App\Entity\VotingPlatform\Designation\Designation;
use App\VotingPlatform\Designation\DesignationTypeEnum;
use Ramsey\Uuid\Uuid;

final class VotingPlatformCandidacyInvitationCreatedMessage extends AbstractVotingPlatformMessage
{
    public static function create(
        Adherent $invited,
        Adherent $candidate,
        Designation $designation,
        string $invitationUrl,
        array $params = [],
    ): self {
        $emailTitle = self::getMailSubjectPrefix($designation);

        return new self(
            Uuid::uuid4(),
            $invited->getEmailAddress(),
            $invited->getFullName(),
            \sprintf('[%s] %s', $emailTitle, self::createSubject($designation, $candidate)),
            array_merge(
                [
                    'email_title' => $emailTitle,
                    'election_type' => $designation->getType(),
                    'invited_first_name' => $invited->getFirstName(),
                    'candidate_first_name' => $candidate->getFirstName(),
                    'candidate_last_name' => $candidate->getLastName(),
                    'candidacy_end_date' => self::dateToString($designation->getCandidacyEndDate()),
                    'page_url' => $invitationUrl,
                ],
                $params
            )
        );
    }

    private static function createSubject(Designation $designation, Adherent $candidate): string
    {
        if (DesignationTypeEnum::NATIONAL_COUNCIL === $designation->getType()) {
            return \sprintf('%s vous a invité(e) à candidater en trinôme', $candidate->getFirstName());
        }

        return \sprintf('%s vous a invité(e) à candidater en binôme', $candidate->getFirstName());
    }
}
