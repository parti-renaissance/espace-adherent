<?php

namespace AppBundle\Mailchimp\Webhook\Handler;

use AppBundle\Mailchimp\Webhook\EventTypeEnum;
use AppBundle\Subscription\SubscriptionHandler;

class AdherentUnsubscribeHandler extends AbstractAdherentHandler
{
    private $subscriptionHandler;

    public function __construct(SubscriptionHandler $handler)
    {
        $this->subscriptionHandler = $handler;
    }

    public function handle(array $data): void
    {
        if ($adherent = $this->getAdherent($data['email'])) {
            $adherent->setEmailUnsubscribed(true);

            $newSubscriptionTypes = $this->calculateNewSubscriptionTypes(
                $adherent->getSubscriptionTypeCodes(),
                []
            );

            $this->subscriptionHandler->handleUpdateSubscription($adherent, $newSubscriptionTypes);
        }
    }

    public function support(string $type, string $listId): bool
    {
        return EventTypeEnum::UNSUBSCRIBE === $type && parent::support($type, $listId);
    }
}
