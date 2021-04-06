<?php

namespace App\Mailer\Message\Coalition;

use App\Entity\Coalition\Cause;
use App\Mailer\Message\Message;
use Ramsey\Uuid\Uuid;

final class CauseApprovalMessage extends AbstractCoalitionMessage
{
    public static function create(Cause $cause, string $causeLink): Message
    {
        $author = $cause->getAuthor();

        return self::updateSenderInfo(new self(
            Uuid::uuid4(),
            $author->getEmailAddress(),
            $author->getFullName(),
            'Votre cause est en ligne',
            [
                'first_name' => self::escape($author->getFirstName()),
                'cause_name' => self::escape($cause->getName()),
                'cause_link' => $causeLink,
            ]
        ));
    }
}
