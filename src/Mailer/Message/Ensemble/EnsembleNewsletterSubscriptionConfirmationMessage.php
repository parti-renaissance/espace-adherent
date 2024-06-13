<?php

namespace App\Mailer\Message\Ensemble;

use Ramsey\Uuid\Uuid;

final class EnsembleNewsletterSubscriptionConfirmationMessage extends AbstractEnsembleMessage
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
