<?php

namespace AppBundle\AdherentMessage\Listener;

use AppBundle\AdherentMessage\Command\AdherentMessageChangeCommand;
use AppBundle\AdherentMessage\Command\AdherentMessageDeleteCommand;
use AppBundle\AdherentMessage\Filter\AdherentMessageFilterInterface;
use AppBundle\Entity\AdherentMessage\AdherentMessageInterface;
use AppBundle\Entity\AdherentMessage\MailchimpCampaign;
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
        $needRecompute = false;

        foreach ($uow->getScheduledEntityUpdates() as $object) {
            if (!$object instanceof AdherentMessageFilterInterface && !$object instanceof AdherentMessageInterface) {
                continue;
            }

            $changeSet = array_keys($uow->getEntityChangeSet($object));

            if (
                ($object instanceof AdherentMessageFilterInterface && $changeSet !== ['synchronized'])
                || ($object instanceof AdherentMessageInterface && array_intersect($changeSet, ['content', 'subject', 'filter']))
            ) {
                $needRecompute = true;
                $object->setSynchronized(false);
            }
        }

        if ($needRecompute) {
            $uow->computeChangeSets();
        }
    }

    private function dispatchMessage(AdherentMessageInterface $object): void
    {
        $this->bus->dispatch(new AdherentMessageChangeCommand($object->getUuid()));
    }
}
