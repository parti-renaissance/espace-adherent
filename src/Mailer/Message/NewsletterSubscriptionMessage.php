<?php

namespace AppBundle\Mailer\Message;

use AppBundle\Entity\NewsletterSubscription;
use Ramsey\Uuid\Uuid;

final class NewsletterSubscriptionMessage extends Message
{
    public static function create(NewsletterSubscription $subscription): self
    {
        return new self(
            Uuid::uuid4(),
            $subscription->getEmail(),
            null
        );
    }
}
