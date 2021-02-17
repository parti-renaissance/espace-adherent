<?php

namespace App\Mailer\Message;

use App\Entity\Adherent;
use App\Entity\VotingPlatform\Designation\Designation;
use App\VotingPlatform\Designation\DesignationTypeEnum;
use Ramsey\Uuid\Uuid;

final class VotingPlatformPartialElectionIsOpenMessage extends Message
{
    /**
     * @param Adherent[] $adherents
     */
    public static function create(
        Designation $designation,
        string $messageContent,
        string $name,
        array $adherents,
        string $url
    ): self {
        $first = array_shift($adherents);

        $params = [
            'vote_end_date' => static::formatDate($designation->getVoteEndDate(), 'EEEE d MMMM y, HH\'h\'mm'),
            'name' => $name,
            'election_type' => $designation->getType(),
            'page_url' => $url,
            'message_content' => $messageContent,
        ];

        $message = new self(
            Uuid::uuid4(),
            $first->getEmailAddress(),
            $first->getFullName(),
            sprintf('[%s] Vous pouvez candidater !', DesignationTypeEnum::COMMITTEE_SUPERVISOR === $designation->getType() ? 'Élections internes' : 'Désignations'),
            $params,
            [
                'first_name' => $first->getFirstName(),
            ],
            null,
            null,
            $params
        );

        foreach ($adherents as $adherent) {
            $message->addRecipient($adherent->getEmailAddress(), $adherent->getFullName(), [
                'first_name' => $adherent->getFirstName(),
            ]);
        }

        return $message;
    }
}
