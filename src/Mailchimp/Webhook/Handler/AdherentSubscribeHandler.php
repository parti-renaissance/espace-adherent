<?php

namespace AppBundle\Mailchimp\Webhook\Handler;

use AppBundle\Mailchimp\MailchimpSubscriptionLabelMapping;
use AppBundle\Mailchimp\Webhook\EventTypeEnum;
use AppBundle\Membership\UserEvent;
use AppBundle\Membership\UserEvents;
use AppBundle\Subscription\SubscriptionHandler;
use AppBundle\Subscription\SubscriptionTypeEnum;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class AdherentSubscribeHandler extends AbstractAdherentHandler
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

            $adherent->setEmailUnsubscribed(false);

            $newSubscriptionTypes = $this->calculateNewSubscriptionTypes(
                $oldEmailsSubscriptions = $adherent->getSubscriptionTypeCodes(),
                MailchimpSubscriptionLabelMapping::getMailchimpLabels(SubscriptionTypeEnum::DEFAULT_EMAIL_TYPES)
            );

            $this->subscriptionHandler->handleUpdateSubscription($adherent, $newSubscriptionTypes);

            $this->dispatcher->dispatch(UserEvents::USER_UPDATE_SUBSCRIPTIONS, new UserEvent($adherent, null, null, $oldEmailsSubscriptions));
        }
    }

    public function support(string $type, string $listId): bool
    {
        return EventTypeEnum::SUBSCRIBE === $type && parent::support($type, $listId);
    }
}
