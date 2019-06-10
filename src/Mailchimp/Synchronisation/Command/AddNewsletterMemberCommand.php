<?php

namespace AppBundle\Mailchimp\Synchronisation\Command;

use AppBundle\Mailchimp\SynchronizeMessageInterface;

class AddNewsletterMemberCommand implements SynchronizeMessageInterface
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
