<?php

namespace AppBundle\Mailjet\Message;

use AppBundle\Entity\NewsletterSubscription;
use Ramsey\Uuid\Uuid;

final class NewsletterSubscriptionMessage extends MailjetMessage
{
    public static function createFromSubscription(NewsletterSubscription $subscription): self
    {
        return new static(
            Uuid::uuid4(),
            '54637',
            $subscription->getEmail(),
            null,
            'Je marche !'
        );
    }
}
