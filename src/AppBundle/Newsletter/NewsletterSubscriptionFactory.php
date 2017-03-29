<?php

namespace AppBundle\Newsletter;

use AppBundle\Entity\NewsletterSubscription;

class NewsletterSubscriptionFactory
{
    public function create(string $email, string $postalCode): NewsletterSubscription
    {
        $subscription = new NewsletterSubscription();
        $subscription->setEmail($email);
        $subscription->setPostalCode($postalCode);

        return $subscription;
    }
}
