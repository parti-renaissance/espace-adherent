<?php

declare(strict_types=1);

namespace App\Newsletter\Command;

use App\Mailchimp\SynchronizeMessageInterface;

class MailchimpSyncNewsletterSubscriptionEntityCommand implements SynchronizeMessageInterface
{
    private string $newsletterSubscriptionClass;
    private int $newsletterSubscriptionId;

    public function __construct(string $newsletterSubscriptionClass, int $newsletterSubscriptionId)
    {
        $this->newsletterSubscriptionClass = $newsletterSubscriptionClass;
        $this->newsletterSubscriptionId = $newsletterSubscriptionId;
    }

    public function getNewsletterSubscriptionClass(): string
    {
        return $this->newsletterSubscriptionClass;
    }

    public function getNewsletterSubscriptionId(): int
    {
        return $this->newsletterSubscriptionId;
    }
}
