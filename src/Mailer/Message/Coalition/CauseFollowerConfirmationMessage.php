<?php

namespace App\Mailer\Message\Coalition;

use App\Entity\Coalition\CauseFollower;
use App\Mailer\Message\Message;
use Ramsey\Uuid\Uuid;

final class CauseFollowerConfirmationMessage extends AbstractCoalitionMessage
{
    public static function create(CauseFollower $causeFollower, string $causeLink): Message
    {
        $adherent = $causeFollower->getAdherent();
        $cause = $causeFollower->getCause();

        return self::updateSenderInfo(new self(
            Uuid::uuid4(),
            $adherent->getEmailAddress(),
            $adherent->getFullName(),
            'L\'aventure débute maintenant !',
            [
                'first_name' => self::escape($adherent->getFirstName()),
                'author_first_name' => self::escape($cause->getAuthor()->getFirstName()),
                'cause_name' => self::escape($cause->getName()),
                'cause_link' => $causeLink,
            ]
        ));
    }
}
