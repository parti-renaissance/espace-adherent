<?php

declare(strict_types=1);

namespace App\Mailer\Message;

use App\Entity\Adherent;
use App\Entity\VotingPlatform\Designation\Designation;
use Ramsey\Uuid\Uuid;

final class VotingPlatformPartialElectionIsOpenMessage extends AbstractVotingPlatformMessage
{
    /**
     * @param Adherent[] $adherents
     */
    public static function create(
        Designation $designation,
        string $messageContent,
        array $adherents,
        string $url = '',
    ): self {
        $first = array_shift($adherents);

        $params = [
            'candidature_end_date' => static::formatDate($designation->getCandidacyEndDate(), 'EEEE d MMMM y, HH\'h\'mm'),
            'vote_start_date' => static::formatDate($designation->getVoteStartDate(), 'EEEE d MMMM y, HH\'h\'mm'),
            'vote_end_date' => static::formatDate($designation->getVoteEndDate(), 'EEEE d MMMM y, HH\'h\'mm'),
            'election_type' => $designation->getDenomination(false, true).'s',
            'mail_subject' => $designation->isCommitteeTypes() ? 'Candidatez dans votre comité !' : 'Candidatez dans votre Conseil territorial !',
            'message_content' => $messageContent,
            'page_url' => $url,
        ];

        $message = new self(
            Uuid::uuid4(),
            $first->getEmailAddress(),
            $first->getFullName(),
            \sprintf(
                '[%s] %s',
                self::getMailSubjectPrefix($designation, true),
                $designation->isCommitteeTypes() ? 'Candidatez dans votre comité !' : 'Candidatez dans votre Conseil territorial !'
            ),
            $params,
            [],
            null,
            null,
            $params
        );

        foreach ($adherents as $adherent) {
            $message->addRecipient($adherent->getEmailAddress(), $adherent->getFullName());
        }

        return $message;
    }
}
