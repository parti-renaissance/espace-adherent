<?php

namespace App\CitizenProject\EventListener;

use App\CitizenProject\CitizenProjectEvent;
use App\Events;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface;
use OldSound\RabbitMqBundle\RabbitMq\ProducerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class CitizenProjectSubscriber implements EventSubscriberInterface
{
    private $producer;
    private $serializer;

    public function __construct(ProducerInterface $producer, SerializerInterface $serializer)
    {
        $this->producer = $producer;
        $this->serializer = $serializer;
    }

    public function publishCitizenProjectCreated(CitizenProjectEvent $event): void
    {
        $this->producer->publish($this->serialize($event), Events::CITIZEN_PROJECT_CREATED);
    }

    public function publishCitizenProjectUpdated(CitizenProjectEvent $event): void
    {
        $this->producer->publish($this->serialize($event), Events::CITIZEN_PROJECT_UPDATED);
    }

    public function publishCitizenProjectDeleted(CitizenProjectEvent $event): void
    {
        $body = json_encode(['uuid' => $event->getCitizenProject()->getUuid()->toString()]);

        $this->producer->publish($body, Events::CITIZEN_PROJECT_DELETED);
    }

    public function serialize(CitizenProjectEvent $event): string
    {
        return $this->serializer->serialize(
            $event->getCitizenProject(),
            'json',
            SerializationContext::create()->setGroups(['citizen_project_read'])
        );
    }

    public static function getSubscribedEvents()
    {
        return [
            // Api Synchronization should be done after all others subscribers so we put the lowest priority
            Events::CITIZEN_PROJECT_CREATED => [['publishCitizenProjectCreated', -512]],
            Events::CITIZEN_PROJECT_UPDATED => [['publishCitizenProjectUpdated', -512]],
            Events::CITIZEN_PROJECT_DELETED => [['publishCitizenProjectDeleted', -512]],
        ];
    }
}
