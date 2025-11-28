<?php

declare(strict_types=1);

namespace App\Newsletter\Command;

use App\Mailchimp\SynchronizeMessageInterface;

class MailchimpSyncLegislativeNewsletterCommand implements SynchronizeMessageInterface
{
    private int $subscriptionId;

    public function __construct(int $subscriptionId)
    {
        $this->subscriptionId = $subscriptionId;
    }

    public function getSubscriptionId(): int
    {
        return $this->subscriptionId;
    }
}
