<?php

declare(strict_types=1);

namespace App\Mailer\Message\Renaissance;

use Symfony\Component\Uid\Uuid;

final class RenaissanceNewsletterSubscriptionConfirmationMessage extends AbstractRenaissanceMessage
{
    public static function create(string $email, string $confirmationLink): self
    {
        return new self(
            Uuid::v4(),
            $email,
            null,
            'Confirmez votre adresse email',
            [],
            ['confirmation_link' => $confirmationLink]
        );
    }
}
