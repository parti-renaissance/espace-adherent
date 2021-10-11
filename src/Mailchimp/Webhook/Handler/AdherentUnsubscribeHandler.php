<?php

namespace App\Mailchimp\Webhook\Handler;

use App\Mailchimp\Webhook\EventTypeEnum;
use App\Membership\UserEvent;
use App\Membership\UserEvents;
use App\Subscription\SubscriptionHandler;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class AdherentUnsubscribeHandler extends AbstractAdherentHandler
{
    private SubscriptionHandler $subscriptionHandler;
    private EventDispatcherInterface $dispatcher;

    public function __construct(SubscriptionHandler $handler, EventDispatcherInterface $dispatcher)
    {
        $this->subscriptionHandler = $handler;
        $this->dispatcher = $dispatcher;
    }

    public function handle(array $data): void
    {
        if ($adherent = $this->getAdherent($data['email'])) {
            $this->dispatcher->dispatch(new UserEvent($adherent), UserEvents::USER_BEFORE_UPDATE);

            $adherent->setEmailUnsubscribed(true);

            $newSubscriptionTypes = $this->calculateNewSubscriptionTypes(
                $adherent->getSubscriptionTypeCodes(),
                []
            );

            $oldEmailsSubscriptions = $adherent->getSubscriptionTypes();

            $this->subscriptionHandler->handleUpdateSubscription($adherent, $newSubscriptionTypes);

            $this->dispatcher->dispatch(new UserEvent($adherent, null, null, $oldEmailsSubscriptions), UserEvents::USER_UPDATE_SUBSCRIPTIONS);
        }
    }

    public function support(string $type, string $listId): bool
    {
        return EventTypeEnum::UNSUBSCRIBE === $type && parent::support($type, $listId);
    }
}
