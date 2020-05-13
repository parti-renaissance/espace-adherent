<?php

namespace App\AdherentMessage\Listener;

use App\AdherentMessage\Command\AdherentMessageChangeCommand;
use App\AdherentMessage\Command\AdherentMessageDeleteCommand;
use App\AdherentMessage\Filter\AdherentMessageFilterInterface;
use App\Entity\AdherentMessage\AdherentMessageInterface;
use App\Entity\AdherentMessage\MailchimpCampaign;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\OnFlushEventArgs;
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
            Events::onFlush,
            Events::postUpdate,
            Events::postRemove,
        ];
    }

    public function postRemove(LifecycleEventArgs $args): void
    {
        $object = $args->getObject();

        if ($object instanceof MailchimpCampaign && $object->getExternalId()) {
            $this->bus->dispatch(new AdherentMessageDeleteCommand($object->getExternalId()));
        }
    }

    public function postPersist(LifecycleEventArgs $args): void
    {
        $object = $args->getObject();

        if ($object instanceof AdherentMessageInterface) {
            $this->dispatchMessage($object);
        }
    }

    public function postUpdate(LifecycleEventArgs $args): void
    {
        $object = $args->getObject();

        if ($object instanceof AdherentMessageInterface && false === $object->isSynchronized()) {
            $this->dispatchMessage($object);
        } elseif ($object instanceof AdherentMessageFilterInterface && false === $object->isSynchronized()) {
            $this->dispatchMessage($object->getMessage());
        }
    }

    public function onFlush(OnFlushEventArgs $args): void
    {
        $em = $args->getEntityManager();
        $uow = $em->getUnitOfWork();

        foreach ($uow->getScheduledEntityUpdates() as $object) {
            if (!$object instanceof AdherentMessageFilterInterface) {
                continue;
            }

            $changeSet = array_keys($uow->getEntityChangeSet($object));

            if ($changeSet !== ['synchronized']) {
                $object->setSynchronized(false);
            }
        }
    }

    private function dispatchMessage(AdherentMessageInterface $object): void
    {
        $this->bus->dispatch(new AdherentMessageChangeCommand($object->getUuid()));
    }
}
