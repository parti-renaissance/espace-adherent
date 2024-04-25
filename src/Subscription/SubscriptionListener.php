<?php

namespace App\Subscription;

use App\History\EmailSubscriptionHistoryHandler;
use App\Membership\Event\UserEvent;
use App\Membership\UserEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class SubscriptionListener implements EventSubscriberInterface
{
    public function __construct(
        private readonly SubscriptionHandler $subscriptionHandler,
        private readonly EmailSubscriptionHistoryHandler $emailSubscriptionHistoryHandler
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            UserEvents::USER_CREATED => 'onUserCreated',
            UserEvents::USER_SWITCH_TO_ADHERENT => 'addSubscriptionTypeToAdherent',
            UserEvents::USER_UPDATE_SUBSCRIPTIONS => ['addSubscriptionTypeToAdherent', 1024],
        ];
    }

    public function onUserCreated(UserEvent $event): void
    {
        $this->addSubscriptionTypeToAdherent($event);
        $this->emailSubscriptionHistoryHandler->handleSubscriptions($event->getUser());
    }

    public function addSubscriptionTypeToAdherent(UserEvent $event): void
    {
        if (false === $event->allowEmailNotifications() && false === $event->allowMobileNotifications()) {
            return;
        }

        $this->subscriptionHandler->addDefaultTypesToAdherent(
            $event->getUser(),
            $event->allowEmailNotifications() ?? false,
            $event->allowMobileNotifications() ?? false
        );
    }
}
