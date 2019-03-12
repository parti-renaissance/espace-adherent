<?php

namespace AppBundle\Mailer\Message;

use AppBundle\Entity\MyEuropeInvitation;
use Ramsey\Uuid\Uuid;

final class MyEuropeMessage extends Message
{
    public static function createFromInvitation(MyEuropeInvitation $invitation): self
    {
        $message = new self(
            Uuid::uuid4(),
            '135119',
            $invitation->getFriendEmailAddress(),
            null,
            $invitation->getMailSubject(),
            ['message' => $invitation->getMailBody()]
        );

        $message->setReplyTo($invitation->getAuthorEmailAddress());
        $message->setSenderName($invitation->getAuthorFirstName().' '.$invitation->getAuthorLastName());
        $message->addCC($invitation->getAuthorEmailAddress());

        return $message;
    }
}
