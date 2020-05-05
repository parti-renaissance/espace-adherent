<?php

namespace AppBundle\Mailer\Message;

use AppBundle\Entity\NewsletterSubscription;
use Ramsey\Uuid\Uuid;

final class NewsletterSubscriptionConfirmationMessage extends Message
{
    public static function create(NewsletterSubscription $subscription, string $confirmationLink): self
    {
        return new self(
            Uuid::uuid4(),
            $subscription->getEmail(),
            null,
            'Confirmez votre adresse email',
            [],
            static::getRecipientVars($confirmationLink)
        );
    }

    private static function getRecipientVars(string $confirmationLink): array
    {
        return [
            'confirmation_link' => $confirmationLink,
        ];
    }
}
