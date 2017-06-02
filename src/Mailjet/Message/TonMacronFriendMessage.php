<?php

namespace AppBundle\Mailjet\Message;

use AppBundle\Entity\TonMacronFriendInvitation;
use Ramsey\Uuid\Uuid;

final class TonMacronFriendMessage extends MailjetMessage
{
    public static function createFromInvitation(TonMacronFriendInvitation $invitation): self
    {
        $message = new self(
            Uuid::uuid4(),
            '135119',
            $invitation->getFriendEmailAddress(),
            null,
            $invitation->getMailSubject(),
            ['message' => $invitation->getMailBody()],
            [],
            null,
            $invitation->getUuid()
        );

        $message->setReplyTo($invitation->getAuthorEmailAddress());
        $message->setSenderName(self::fixMailjetParsing($invitation->getAuthorFirstName().' '.$invitation->getAuthorLastName()));
        $message->addCC($invitation->getAuthorEmailAddress());

        return $message;
    }
}
