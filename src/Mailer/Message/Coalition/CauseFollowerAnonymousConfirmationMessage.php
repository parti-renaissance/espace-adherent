<?php

namespace App\Mailer\Message\Coalition;

use App\Entity\Coalition\CauseFollower;
use App\Mailer\Message\Message;
use Ramsey\Uuid\Uuid;

final class CauseFollowerAnonymousConfirmationMessage extends AbstractCoalitionMessage
{
    public static function create(CauseFollower $causeFollower, string $causeLink, string $createAccountLink): Message
    {
        $cause = $causeFollower->getCause();

        return self::updateSenderInfo(new self(
            Uuid::uuid4(),
            $causeFollower->getEmailAddress(),
            $causeFollower->getFirstName(),
            'L\'aventure débute maintenant !',
            [
                'first_name' => self::escape($causeFollower->getFirstName()),
                'author_first_name' => self::escape($cause->getAuthor()->getFirstName()),
                'cause_name' => self::escape($cause->getName()),
                'cause_link' => $causeLink,
                'create_account_link' => $createAccountLink,
            ]
        ));
    }
}
