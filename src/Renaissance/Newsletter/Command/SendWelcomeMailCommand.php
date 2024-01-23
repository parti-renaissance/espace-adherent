<?php

namespace App\Renaissance\Newsletter\Command;

use App\Entity\Renaissance\NewsletterSubscription;

class SendWelcomeMailCommand
{
    public function __construct(public readonly NewsletterSubscription $newsletterSubscription)
    {
    }
}
