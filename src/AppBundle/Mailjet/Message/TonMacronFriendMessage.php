<?php

namespace AppBundle\Mailjet\Message;

use AppBundle\Entity\TonMacronFriendInvitation;
use Ramsey\Uuid\Uuid;

final class TonMacronFriendMessage extends MailjetMessage
{
    public static function createFromInvitation(TonMacronFriendInvitation $invitation): self
    {
        $message = new static(
            Uuid::uuid4(),
            '135119',
            $invitation->getFriendEmailAddress(),
            self::fixMailjetParsing($invitation->getAuthorFirstName().' '.$invitation->getAuthorLastName()),
            $invitation->getMailSubject(),
            ['message' => $invitation->getMailBody()],
            [],
            null,
            $invitation->getUuid()
        );
        $message->addCC($invitation->getAuthorEmailAddress());

        return $message;
    }
}
