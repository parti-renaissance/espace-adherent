<?php

namespace AppBundle\ApplicationRequest\Listener;

use AppBundle\Entity\ApplicationRequest\ApplicationRequest;
use AppBundle\Mailchimp\Synchronisation\Command\AddApplicationRequestCandidateCommand;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Events;
use Symfony\Component\Messenger\MessageBusInterface;

class ApplicationRequestChangeSubscriber implements EventSubscriber
{
    private $bus;

    public function __construct(MessageBusInterface $bus)
    {
        $this->bus = $bus;
    }

    public function getSubscribedEvents()
    {
        return [
            Events::postPersist,
            Events::postUpdate,
        ];
    }

    public function postPersist(LifecycleEventArgs $args): void
    {
        $object = $args->getObject();

        if ($object instanceof ApplicationRequest) {
            $this->dispatchMessage($object);
        }
    }

    public function postUpdate(LifecycleEventArgs $args): void
    {
        $object = $args->getObject();

        if ($object instanceof ApplicationRequest) {
            $this->dispatchMessage($object);
        }
    }

    private function dispatchMessage(ApplicationRequest $object): void
    {
        $this->bus->dispatch(new AddApplicationRequestCandidateCommand($object->getId(), \get_class($object)));
    }
}
