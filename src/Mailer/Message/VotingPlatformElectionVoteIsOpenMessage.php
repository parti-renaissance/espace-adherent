<?php

namespace App\Mailer\Message;

use App\Entity\Adherent;
use App\Entity\VotingPlatform\Election;
use App\VotingPlatform\Designation\DesignationTypeEnum;
use Ramsey\Uuid\Uuid;

class VotingPlatformElectionVoteIsOpenMessage extends Message
{
    /**
     * @param Adherent[] $adherents
     */
    public static function create(Election $election, array $adherents, string $url): self
    {
        $first = current($adherents);

        $message = new self(
            Uuid::uuid4(),
            $first->getEmailAddress(),
            $first->getFullName(),
            '[Désignations] Désignez votre binôme d\'adhérents !',
            [
                'vote_end_date' => static::formatDate($election->getVoteEndDate(), 'EEEE d MMMM y, HH\'h\'mm'),
                'name' => $election->getElectionEntity()->getName(),
                'is_copol' => DesignationTypeEnum::COPOL === $election->getDesignationType(),
                'page_url' => $url,
            ],
            [
                'first_name' => $first->getFirstName(),
            ]
        );

        foreach ($adherents as $adherent) {
            $message->addRecipient($adherent->getEmailAddress(), $adherent->getFullName(), [
                'first_name' => $adherent->getFirstName(),
            ]);
        }

        return $message;
    }
}
