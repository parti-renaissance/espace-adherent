<?php

declare(strict_types=1);

namespace App\Mailer\Message\Renaissance\VotingPlatform;

use App\Entity\VotingPlatform\Election;
use App\Mailer\Message\Renaissance\AbstractRenaissanceMessage;
use Ramsey\Uuid\Uuid;

class ResultsReadyMessage extends AbstractRenaissanceMessage
{
    public static function create(Election $election, array $adherents, string $url): self
    {
        $adherent = array_shift($adherents);

        $message = new self(
            Uuid::uuid4(),
            $adherent->getEmailAddress(),
            $adherent->getFullName(),
            'Les résultats sont disponibles',
            [
                'vote_title' => $election->getTitle(),
                'nb_days' => $election->getDesignation()->getResultDisplayDelay(),
                'primary_link' => $url,
            ],
            ['first_name' => $adherent->getFirstName()]
        );

        foreach ($adherents as $adherent) {
            $message->addRecipient($adherent->getEmailAddress(), $adherent->getFullName(), [
                'first_name' => $adherent->getFirstName(),
            ]);
        }

        return $message;
    }
}
