<?php

namespace App\Mailer\Message;

use App\Entity\Adherent;
use Ramsey\Uuid\Uuid;

final class NewsletterAdherentSubscriptionMessage extends Message
{
    public static function create(Adherent $adherent, string $emailNotificationsLink): self
    {
        return new self(
            Uuid::uuid4(),
            $adherent->getEmailAddress(),
            $adherent->getFullName(),
            'Votre demande d\'inscription à notre newsletter n\'a pas été prise en compte',
            [],
            static::getRecipientVars($emailNotificationsLink)
        );
    }

    private static function getRecipientVars(string $emailNotificationsLink): array
    {
        return [
            'email_notifications_link' => $emailNotificationsLink,
        ];
    }
}
