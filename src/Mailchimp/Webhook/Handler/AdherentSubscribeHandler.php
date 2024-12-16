<?php

namespace App\Mailchimp\Webhook\Handler;

use App\Mailchimp\Webhook\EventTypeEnum;
use App\Subscription\SubscriptionHandler;
use App\Subscription\SubscriptionTypeEnum;

class AdherentSubscribeHandler extends AbstractAdherentHandler
{
    public function __construct(private readonly SubscriptionHandler $subscriptionHandler)
    {
    }

    public function handle(array $data): void
    {
        if ($adherent = $this->getAdherent($data['email'])) {
            $adherent->setEmailUnsubscribed(false);

            $this->subscriptionHandler->handleUpdateSubscription($adherent, SubscriptionTypeEnum::DEFAULT_EMAIL_TYPES);
        }
    }

    public function support(string $type, string $listId): bool
    {
        return EventTypeEnum::SUBSCRIBE === $type && parent::support($type, $listId);
    }
}
