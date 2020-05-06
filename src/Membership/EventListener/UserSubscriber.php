<?php

namespace App\Membership\EventListener;

use App\Membership\UserEvent;
use App\Membership\UserEvents;
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

    public function publishUserEvent(UserEvent $event, string $eventName): void
    {
        $this->producer->publish($this->serialize($event), $eventName);
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
            UserEvents::USER_CREATED => 'publishUserEvent',
            UserEvents::USER_UPDATED => 'publishUserEvent',
            UserEvents::USER_DELETED => 'publishUserEvent',
            UserEvents::USER_UPDATE_SUBSCRIPTIONS => 'publishUserUpdateSubscription',
        ];
    }
}
