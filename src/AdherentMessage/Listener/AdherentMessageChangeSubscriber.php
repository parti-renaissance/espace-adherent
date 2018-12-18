<?php

namespace AppBundle\AdherentMessage\Listener;

use AppBundle\AdherentMessage\Handler\AdherentMessageChangeCommand;
use AppBundle\AdherentMessage\Handler\AdherentMessageDeleteCommand;
use AppBundle\Entity\AdherentMessage\AdherentMessageInterface;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Events;
use Symfony\Component\Messenger\MessageBusInterface;

class AdherentMessageChangeSubscriber implements EventSubscriber
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
            Events::preUpdate,
            Events::postUpdate,
            Events::postRemove,
        ];
    }

    public function postRemove(LifecycleEventArgs $args): void
    {
        $object = $args->getObject();

        if (!($object instanceof AdherentMessageInterface) || !$object->getExternalId()) {
            return;
        }

        $this->bus->dispatch(new AdherentMessageDeleteCommand($object->getExternalId()));
    }

    public function postPersist(LifecycleEventArgs $args): void
    {
        $object = $args->getObject();

        if (!$object instanceof AdherentMessageInterface) {
            return;
        }

        $this->dispatchMessage($object);
    }

    public function preUpdate(LifecycleEventArgs $args): void
    {
        $object = $args->getObject();

        if (!$object instanceof AdherentMessageInterface) {
            return;
        }

        $changes = $args->getEntityManager()->getUnitOfWork()->getEntityChangeSet($object);

        if (isset($changes['content']) || isset($changes['subject'])) {
            $object->setSynchronized(false);
        }
    }

    public function postUpdate(LifecycleEventArgs $args): void
    {
        $object = $args->getObject();

        if (!$object instanceof AdherentMessageInterface || $object->isSynchronized()) {
            return;
        }

        $this->dispatchMessage($args->getObject());
    }

    private function dispatchMessage($object): void
    {
        $this->bus->dispatch(new AdherentMessageChangeCommand($object->getUuid()));
    }
}
