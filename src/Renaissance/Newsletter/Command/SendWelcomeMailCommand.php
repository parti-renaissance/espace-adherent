<?php

namespace App\Renaissance\Newsletter\Command;

use App\Entity\Renaissance\NewsletterSubscription;

class SendWelcomeMailCommand
{
    public NewsletterSubscription $newsletterSubscription;

    public function __construct(NewsletterSubscription $newsletterSubscription)
    {
        $this->newsletterSubscription = $newsletterSubscription;
    }
}
