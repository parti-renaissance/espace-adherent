<?php

namespace App\Mailer\Message\BesoinDEurope;

use Ramsey\Uuid\Uuid;

final class EuNewsletterSubscriptionConfirmationMessage extends AbstractBesoinDEuropeMessage
{
    public static function create(string $email, string $confirmationLink): self
    {
        return new self(
            Uuid::uuid4(),
            $email,
            null,
            'Confirmez votre adresse email',
            ['confirmation_link' => $confirmationLink]
        );
    }
}
