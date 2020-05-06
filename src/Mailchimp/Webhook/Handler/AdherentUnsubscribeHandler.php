<?php

namespace App\Mailchimp\Webhook\Handler;

use App\Mailchimp\Webhook\EventTypeEnum;
use App\Membership\UserEvent;
use App\Membership\UserEvents;
use App\Subscription\SubscriptionHandler;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class AdherentUnsubscribeHandler extends AbstractAdherentHandler
{
    private $subscriptionHandler;
    private $dispatcher;

    public function __construct(SubscriptionHandler $handler, EventDispatcherInterface $dispatcher)
    {
        $this->subscriptionHandler = $handler;
        $this->dispatcher = $dispatcher;
    }

    public function handle(array $data): void
    {
        if ($adherent = $this->getAdherent($data['email'])) {
            $this->dispatcher->dispatch(UserEvents::USER_BEFORE_UPDATE, new UserEvent($adherent));

            $adherent->setEmailUnsubscribed(true);

            $newSubscriptionTypes = $this->calculateNewSubscriptionTypes(
                $adherent->getSubscriptionTypeCodes(),
                []
            );

            $oldEmailsSubscriptions = $adherent->getSubscriptionTypes();

            $this->subscriptionHandler->handleUpdateSubscription($adherent, $newSubscriptionTypes);

            $this->dispatcher->dispatch(UserEvents::USER_UPDATE_SUBSCRIPTIONS, new UserEvent($adherent, null, null, $oldEmailsSubscriptions));
        }
    }

    public function support(string $type, string $listId): bool
    {
        return EventTypeEnum::UNSUBSCRIBE === $type && parent::support($type, $listId);
    }
}
