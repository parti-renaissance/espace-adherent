<?php

namespace AppBundle\Mailchimp\Webhook\Handler;

use AppBundle\Mailchimp\Webhook\EventTypeEnum;
use AppBundle\Subscription\SubscriptionHandler;

class UnsubscribeHandler extends AbstractAdherentHandler
{
    private $subscriptionHandler;

    public function __construct(SubscriptionHandler $handler)
    {
        $this->subscriptionHandler = $handler;
    }

    public function handle(array $data): void
    {
        if ($adherent = $this->getAdherent($data['email'])) {
            $this->subscriptionHandler->unsubscribeAllMails($adherent);
        }
    }

    public function support(string $type): bool
    {
        return EventTypeEnum::UNSUBSCRIBE === $type;
    }
}
