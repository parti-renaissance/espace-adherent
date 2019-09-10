<?php

namespace AppBundle\Newsletter\Command;

use AppBundle\Mailchimp\SynchronizeMessageInterface;

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
