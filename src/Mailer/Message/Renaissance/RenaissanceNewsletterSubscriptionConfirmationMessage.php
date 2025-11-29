<?php

declare(strict_types=1);

namespace App\Mailer\Message\Renaissance;

use Ramsey\Uuid\Uuid;

final class RenaissanceNewsletterSubscriptionConfirmationMessage extends AbstractRenaissanceMessage
{
    public static function create(string $email, string $confirmationLink): self
    {
        return new self(
            Uuid::uuid4(),
            $email,
            null,
            'Confirmez votre adresse email',
            [],
            ['confirmation_link' => $confirmationLink]
        );
    }
}
