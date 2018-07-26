<?php

namespace AppBundle\Membership\EventListener;

use AppBundle\Membership\UserEvent;
use AppBundle\Membership\UserEvents;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface;
use OldSound\RabbitMqBundle\RabbitMq\ProducerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class UserSubscriber implements EventSubscriberInterface
{
    private $producer;
    private $serializer;

    public function __construct(ProducerInterface $producer, SerializerInterface $serializer)
    {
        $this->producer = $producer;
        $this->serializer = $serializer;
    }

    public function publishUserCreated(UserEvent $event): void
    {
        $this->producer->publish($this->serialize($event), UserEvents::USER_CREATED);
    }

    public function publishUserUpdated(UserEvent $event): void
    {
        $this->producer->publish($this->serialize($event), UserEvents::USER_UPDATED);
    }

    public function publishUserDeleted(UserEvent $event): void
    {
        $body = json_encode(['uuid' => $event->getUser()->getUuid()->toString()]);

        $this->producer->publish($body, UserEvents::USER_DELETED);
    }

    public function publishUserUpdateSubscription(UserEvent $event): void
    {
        if ($event->getSubscriptions() || $event->getUnsubscriptions()) {
            $body = json_encode([
                'uuid' => $event->getUser()->getUuid()->toString(),
                'subscriptions' => $event->getSubscriptions(),
                'unsubscriptions' => $event->getUnsubscriptions(),
            ]);

            $this->producer->publish($body, UserEvents::USER_UPDATE_SUBSCRIPTIONS);
        }
    }

    public function serialize(UserEvent $event): string
    {
        return $this->serializer->serialize($event->getUser(), 'json', SerializationContext::create()->setGroups(['public']));
    }

    public static function getSubscribedEvents()
    {
        return [
            UserEvents::USER_CREATED => 'publishUserCreated',
            UserEvents::USER_UPDATED => 'publishUserUpdated',
            UserEvents::USER_DELETED => 'publishUserDeleted',
            UserEvents::USER_UPDATE_SUBSCRIPTIONS => 'publishUserUpdateSubscription',
        ];
    }
}
