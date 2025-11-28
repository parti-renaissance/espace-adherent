<?php

declare(strict_types=1);

namespace App\Subscription;

use App\History\EmailSubscriptionHistoryHandler;
use App\Membership\Event\UserEvent;
use App\Membership\UserEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class SubscriptionListener implements EventSubscriberInterface
{
    public function __construct(
        private readonly SubscriptionHandler $subscriptionHandler,
        private readonly EmailSubscriptionHistoryHandler $emailSubscriptionHistoryHandler,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            UserEvents::USER_CREATED => 'onUserCreated',
        ];
    }

    public function onUserCreated(UserEvent $event): void
    {
        $this->addSubscriptionTypeToAdherent($event);
        $this->emailSubscriptionHistoryHandler->handleSubscriptions($event->getAdherent());
    }

    public function addSubscriptionTypeToAdherent(UserEvent $event): void
    {
        if (!$event->allowEmailNotifications() && !$event->allowMobileNotifications()) {
            return;
        }

        $this->subscriptionHandler->addDefaultTypesToAdherent(
            $event->getAdherent(),
            $event->allowEmailNotifications() ?? false,
            $event->allowMobileNotifications() ?? false
        );
    }
}
