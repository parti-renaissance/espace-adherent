<?php

namespace AppBundle\Mailchimp\Synchronisation\Command;

use AppBundle\Mailchimp\SynchronizeMessageInterface;

class UpdateAdherentCommand implements SynchronizeMessageInterface
{
    private $mail;
    private $subscriptionTypeLabels;
    private $interestLabels;
    private $unsubscribe;

    public function __construct(string $mail, string $subscriptionTypeLabels, string $interestLabels, bool $unsubscribe)
    {
        $this->mail = $mail;
        $this->subscriptionTypeLabels = $subscriptionTypeLabels;
        $this->interestLabels = $interestLabels;
        $this->unsubscribe = $unsubscribe;
    }

    public function getMail(): string
    {
        return $this->mail;
    }

    public function getSubscriptionTypeLabels(): string
    {
        return $this->subscriptionTypeLabels;
    }

    public function getInterestLabels(): string
    {
        return $this->interestLabels;
    }

    public function isUnsubscribe(): bool
    {
        return $this->unsubscribe;
    }
}
