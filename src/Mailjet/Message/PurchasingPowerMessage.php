<?php

namespace AppBundle\Mailjet\Message;

use AppBundle\Entity\PurchasingPowerInvitation;
use Ramsey\Uuid\Uuid;

final class PurchasingPowerMessage extends MailjetMessage
{
    public static function createFromInvitation(PurchasingPowerInvitation $invitation): self
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
        $message->setSenderName(self::fixMailjetParsing($invitation->getAuthorFirstName().' '.$invitation->getAuthorLastName()));
        $message->addCC($invitation->getAuthorEmailAddress());

        return $message;
    }
}
