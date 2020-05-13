<?php

namespace App\Committee\EventListener;

use App\Committee\CommitteeEvent;
use App\Events;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface;
use OldSound\RabbitMqBundle\RabbitMq\ProducerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class CommitteeSubscriber implements EventSubscriberInterface
{
    private $producer;
    private $serializer;

    public function __construct(ProducerInterface $producer, SerializerInterface $serializer)
    {
        $this->producer = $producer;
        $this->serializer = $serializer;
    }

    public function publishCommitteeCreated(CommitteeEvent $event): void
    {
        $this->producer->publish($this->serialize($event), Events::COMMITTEE_CREATED);
    }

    public function publishCommitteeUpdated(CommitteeEvent $event): void
    {
        $this->producer->publish($this->serialize($event), Events::COMMITTEE_UPDATED);
    }

    public function publishCommitteeDeleted(CommitteeEvent $event): void
    {
        $body = json_encode(['uuid' => $event->getCommittee()->getUuid()->toString()]);

        $this->producer->publish($body, Events::COMMITTEE_DELETED);
    }

    public function serialize(CommitteeEvent $event): string
    {
        return $this->serializer->serialize(
            $event->getCommittee(),
            'json',
            SerializationContext::create()->setGroups(['committee_read'])
        );
    }

    public static function getSubscribedEvents()
    {
        return [
            // Api Synchronization should be done after all others subscribers so we put the lowest priority
            Events::COMMITTEE_CREATED => [['publishCommitteeCreated', -512]],
            Events::COMMITTEE_UPDATED => [['publishCommitteeUpdated', -512]],
            Events::COMMITTEE_DELETED => [['publishCommitteeDeleted', -512]],
        ];
    }
}
