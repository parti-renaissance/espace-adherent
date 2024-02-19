<?php

namespace App\Mailer\Message\Renaissance;

use App\Mailer\Message\Message;
use Ramsey\Uuid\Uuid;

final class EuNewsletterSubscriptionConfirmationMessage extends Message implements EuMessageInterface
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
