<?php

namespace App\Newsletter\Command;

use App\Mailchimp\SynchronizeMessageInterface;

class MailchimpSyncNewsletterSubscriptionEntityCommand implements SynchronizeMessageInterface
{
    private $newsletterSubscriptionId;

    public function __construct(int $newsletterSubscriptionId)
    {
        $this->newsletterSubscriptionId = $newsletterSubscriptionId;
    }

    public function getNewsletterSubscriptionId(): int
    {
        return $this->newsletterSubscriptionId;
    }
}
