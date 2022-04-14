<?php

namespace App\Mailer\Message;

use App\Entity\LegislativeNewsletterSubscription;
use Ramsey\Uuid\Uuid;

final class LegislativeNewsletterSubscriptionConfirmationMessage extends Message
{
    public static function create(LegislativeNewsletterSubscription $subscription, string $confirmationLink): self
    {
        return new self(
            Uuid::uuid4(),
            $subscription->getEmailAddress(),
            $subscription->getFirstName(),
            'Confirmez votre adresse email',
            [],
            [
                'confirmation_link' => $confirmationLink,
            ]
        );
    }
}
