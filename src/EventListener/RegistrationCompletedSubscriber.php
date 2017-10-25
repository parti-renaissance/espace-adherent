<?php

namespace AppBundle\EventListener;

use AppBundle\Membership\AdherentAccountWasCreatedEvent;
use AppBundle\Membership\AdherentEvents;
use OldSound\RabbitMqBundle\RabbitMq\ProducerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class RegistrationCompletedSubscriber implements EventSubscriberInterface
{
    private $producer;

    public function __construct(ProducerInterface $producer)
    {
        $this->producer = $producer;
    }

    public static function getSubscribedEvents()
    {
        return [AdherentEvents::REGISTRATION_COMPLETED => 'synchroniseWithAuth'];
    }

    public function synchroniseWithAuth(AdherentAccountWasCreatedEvent $event)
    {
        $request = $event->getMembershipRequest();

        $message = [
            'emailAddress' => $request->getEmailAddress(),
            'firstName' => $request->firstName,
            'lastName' => $request->lastName,
            'zipCode' => $request->getAddress()->getPostalCode(),
            'plainPassword' => $request->password,
        ];

        $this->producer->publish(\GuzzleHttp\json_encode($message));
    }
}
