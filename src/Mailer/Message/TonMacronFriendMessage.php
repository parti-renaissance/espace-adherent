<?php

namespace App\Mailer\Message;

use App\Entity\TonMacronFriendInvitation;
use Ramsey\Uuid\Uuid;

final class TonMacronFriendMessage extends Message
{
    public static function createFromInvitation(TonMacronFriendInvitation $invitation): self
    {
        $message = new self(
            Uuid::uuid4(),
            $invitation->getFriendEmailAddress(),
            null,
            $invitation->getMailSubject(),
            ['message' => $invitation->getMailBody()],
            [],
            $invitation->getAuthorEmailAddress(),
            'basic-message'
        );

        $message->setSenderName($invitation->getAuthorFirstName().' '.$invitation->getAuthorLastName());
        $message->addCC($invitation->getAuthorEmailAddress());

        return $message;
    }
}
