<?php

namespace App\Mailer\Message;

use App\Entity\NewsletterSubscriptionInterface;
use Ramsey\Uuid\Uuid;

final class NewsletterSubscriptionConfirmationMessage extends Message
{
    public static function create(NewsletterSubscriptionInterface $subscription, string $confirmationLink): self
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
