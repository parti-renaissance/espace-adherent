<?php

namespace AppBundle\Mailer\Message;

use AppBundle\Entity\PurchasingPowerInvitation;
use Ramsey\Uuid\Uuid;

final class PurchasingPowerMessage extends Message
{
    public static function create(PurchasingPowerInvitation $invitation): self
    {
        $message = new self(
            Uuid::uuid4(),
            $invitation->getFriendEmailAddress(),
            null,
            static::getTemplateVars($invitation),
            [],
            $invitation->getAuthorEmailAddress()
        );

        $message->setSenderName($invitation->getAuthorFirstName().' '.$invitation->getAuthorLastName());
        $message->addCC($invitation->getAuthorEmailAddress());

        return $message;
    }

    private static function getTemplateVars(PurchasingPowerInvitation $invitation): array
    {
        return [
            'message' => $invitation->getMailBody(),
        ];
    }
}
