<?php

namespace AppBundle\Mailjet\Message;

use AppBundle\Entity\TonMacronFriendInvitation;
use Ramsey\Uuid\Uuid;

final class TonMacronFriendMessage extends MailjetMessage
{
    public static function createFromInvitation(TonMacronFriendInvitation $invitation, string $body): self
    {
        $message = new static(
            Uuid::uuid4(),
            '000000',
            $invitation->getFriendEmailAddress(),
            null,
            $invitation->getMailSubject(),
            ['message' => $body],
            [],
            null,
            $invitation->getUuid()
        );
        $message->addCC($invitation->getAuthorEmailAddress());

        return $message;
    }
}
