<?php

namespace App\Mailer\Message\Legislatives;

use App\Entity\LegislativeNewsletterSubscription;
use App\Mailer\Message\Message;
use Ramsey\Uuid\Uuid;

final class LegislativeNewsletterSubscriptionConfirmationMessage extends AbstractLegislativeNewsletterMessage
{
    public static function create(LegislativeNewsletterSubscription $subscription, string $confirmationLink): Message
    {
        $message = new self(
            Uuid::uuid4(),
            $subscription->getEmailAddress(),
            $subscription->getFirstName(),
            'Confirmez votre adresse email',
            [],
            [
                'confirmation_link' => $confirmationLink,
            ]
        );

        return self::updateSenderInfo($message);
    }
}
