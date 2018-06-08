<?php

namespace AppBundle\Mailer\Message;

use AppBundle\Entity\TonMacronFriendInvitation;
use Ramsey\Uuid\Uuid;

final class TonMacronFriendMessage extends Message
{
    public static function create(TonMacronFriendInvitation $invitation): self
    {
        $message = new self(
            Uuid::uuid4(),
            $invitation->getFriendEmailAddress(),
            null,
            static::getTemplateVars($invitation),
            ['recipient_first_name' => self::escape($invitation->getFriendFirstName())],
            $invitation->getAuthorEmailAddress()
        );

        $message->setSenderName($invitation->getAuthorFirstName().' '.$invitation->getAuthorLastName());
        $message->addCC($invitation->getAuthorEmailAddress());

        return $message;
    }

    private static function getTemplateVars(TonMacronFriendInvitation $invitation): array
    {
        return [
            'message' => self::escape($invitation->getMailBody()),
        ];
    }
}
