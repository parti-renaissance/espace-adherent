<?php

namespace App\Event\EventListener;

use App\Event\CommitteeEventEvent;
use App\Event\EventEvent;
use App\Events;
use OldSound\RabbitMqBundle\RabbitMq\ProducerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Serializer\SerializerInterface;

class ApiSyncEventSubscriber implements EventSubscriberInterface
{
    private $producer;
    private $serializer;

    public function __construct(ProducerInterface $producer, SerializerInterface $serializer)
    {
        $this->producer = $producer;
        $this->serializer = $serializer;
    }

    public function publishEventCreated(EventEvent $event): void
    {
        if ($event instanceof CommitteeEventEvent && !$event->getEvent()->isPrivate()) {
            $this->producer->publish($this->serialize($event), Events::EVENT_CREATED);
        }
    }

    public function publishEventUpdated(EventEvent $event): void
    {
        if ($event instanceof CommitteeEventEvent && !$event->getEvent()->isPrivate()) {
            $this->producer->publish($this->serialize($event), Events::EVENT_UPDATED);
        }
    }

    public function publishEventDeleted(CommitteeEventEvent $event): void
    {
        $body = json_encode(['uuid' => $event->getEvent()->getUuid()->toString()]);

        $this->producer->publish($body, Events::EVENT_DELETED);
    }

    public function serialize(CommitteeEventEvent $event): string
    {
        return $this->serializer->serialize($event->getEvent(), 'json', ['groups' => ['event_sync']]);
    }

    public static function getSubscribedEvents()
    {
        return [
            // Api Synchronization should be done after all others subscribers so we put the lowest priority
            Events::EVENT_CREATED => [['publishEventCreated', -512]],
            Events::EVENT_UPDATED => [['publishEventUpdated', -512]],
            Events::EVENT_CANCELLED => [['publishEventUpdated', -512]],
            Events::EVENT_DELETED => [['publishEventDeleted', -512]],
        ];
    }
}
