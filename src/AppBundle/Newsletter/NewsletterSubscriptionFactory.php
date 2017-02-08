<?php

namespace AppBundle\Newsletter;

use AppBundle\Entity\NewsletterSubscription;

class NewsletterSubscriptionFactory
{
    public function create(string $email, string $postalCode, string $clientIp = null): NewsletterSubscription
    {
        $subscription = new NewsletterSubscription();
        $subscription->setEmail($email);
        $subscription->setPostalCode($postalCode);
        $subscription->setClientIp($clientIp);

        return $subscription;
    }
}
