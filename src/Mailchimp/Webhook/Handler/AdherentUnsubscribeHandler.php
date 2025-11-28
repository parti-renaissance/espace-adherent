<?php

declare(strict_types=1);

namespace App\Mailchimp\Webhook\Handler;

use App\Mailchimp\Webhook\EventTypeEnum;
use App\Subscription\SubscriptionHandler;

class AdherentUnsubscribeHandler extends AbstractAdherentHandler
{
    public function __construct(private readonly SubscriptionHandler $subscriptionHandler)
    {
    }

    public function handle(array $data): void
    {
        if (!$adherent = $this->getAdherent($data['email'])) {
            return;
        }

        $adherent->setEmailUnsubscribed(true);
        $this->subscriptionHandler->handleUpdateSubscription($adherent, []);
    }

    public function support(string $type, string $listId): bool
    {
        return EventTypeEnum::UNSUBSCRIBE === $type && parent::support($type, $listId);
    }
}
