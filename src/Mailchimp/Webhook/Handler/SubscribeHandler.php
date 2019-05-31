<?php

namespace AppBundle\Mailchimp\Webhook\Handler;

use AppBundle\Mailchimp\Webhook\EventTypeEnum;
use AppBundle\Subscription\SubscriptionHandler;
use AppBundle\Subscription\SubscriptionTypeEnum;

class SubscribeHandler extends AbstractAdherentHandler
{
    private $subscriptionHandler;

    public function __construct(SubscriptionHandler $handler)
    {
        $this->subscriptionHandler = $handler;
    }

    public function handle(array $data): void
    {
        if ($adherent = $this->getAdherent($data['email'])) {
            $adherent->setEmailUnsubscribed(false);

            $newSubscriptionTypes = $this->calculateNewSubscriptionTypes(
                $adherent->getEmailsSubscriptions(),
                SubscriptionTypeEnum::DEFAULT_EMAIL_TYPES
            );

            $this->subscriptionHandler->handleUpdateSubscription($adherent, $newSubscriptionTypes);
        }
    }

    public function support(string $type): bool
    {
        return EventTypeEnum::SUBSCRIBE === $type;
    }
}
