<?php

namespace AppBundle\CitizenAction\EventListener;

use AppBundle\CitizenAction\CitizenActionEvent;
use AppBundle\Events;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface;
use OldSound\RabbitMqBundle\RabbitMq\ProducerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class CitizenActionSubscriber implements EventSubscriberInterface
{
    private $producer;
    private $serializer;

    public function __construct(ProducerInterface $producer, SerializerInterface $serializer)
    {
        $this->producer = $producer;
        $this->serializer = $serializer;
    }

    public function publishCitizenActionCreated(CitizenActionEvent $event): void
    {
        $this->producer->publish($this->serialize($event), Events::CITIZEN_ACTION_CREATED);
    }

    public function publishCitizenActionUpdated(CitizenActionEvent $event): void
    {
        $this->producer->publish($this->serialize($event), Events::CITIZEN_ACTION_UPDATED);
    }

    public function publishCitizenActionDeleted(CitizenActionEvent $event): void
    {
        $body = json_encode(['uuid' => $event->getCitizenAction()->getUuid()->toString()]);

        $this->producer->publish($body, Events::CITIZEN_ACTION_DELETED);
    }

    public function serialize(CitizenActionEvent $event): string
    {
        return $this->serializer->serialize(
            $event->getCitizenAction(),
            'json',
            SerializationContext::create()->setGroups(['citizen_action_read'])
        );
    }

    public static function getSubscribedEvents()
    {
        return [
            // Api Synchronization should be done after all others subscribers so we put the lowest priority
            Events::CITIZEN_ACTION_CREATED => [['publishCitizenActionCreated', -255]],
            Events::CITIZEN_ACTION_UPDATED => [['publishCitizenActionUpdated', -255]],
            Events::CITIZEN_ACTION_DELETED => [['publishCitizenActionDeleted', -255]],
        ];
    }
}
