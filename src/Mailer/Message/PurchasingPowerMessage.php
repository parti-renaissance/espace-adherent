<?php

namespace AppBundle\Mailer\Message;

use AppBundle\Entity\PurchasingPowerInvitation;
use Ramsey\Uuid\Uuid;

final class PurchasingPowerMessage extends Message
{
    public static function createFromInvitation(PurchasingPowerInvitation $invitation): self
    {
        $message = new self(
            Uuid::uuid4(),
            $invitation->getFriendEmailAddress(),
            null,
            self::getTemplateVars($invitation->getMailBody()),
            [],
            $invitation->getAuthorEmailAddress()
        );

        $message->setSenderName($invitation->getAuthorFirstName().' '.$invitation->getAuthorLastName());
        $message->addCC($invitation->getAuthorEmailAddress());

        return $message;
    }

    private static function getTemplateVars(string $message): array
    {
        return [
            'message' => $message,
        ];
    }
}
