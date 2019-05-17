<?php

namespace AppBundle\AdherentMessage\Listener;

use AppBundle\AdherentMessage\Command\AdherentMessageChangeCommand;
use AppBundle\AdherentMessage\Command\AdherentMessageDeleteCommand;
use AppBundle\AdherentMessage\Filter\AdherentMessageFilterInterface;
use AppBundle\Entity\AdherentMessage\AdherentMessageInterface;
use AppBundle\Entity\AdherentMessage\MailchimpCampaign;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
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

    public function preUpdate(PreUpdateEventArgs $event): void
    {
        $object = $event->getObject();

        if ($object instanceof AdherentMessageFilterInterface && array_keys($event->getEntityChangeSet()) !== ['synchronized']) {
            $object->setSynchronized(false);
        } elseif ($object instanceof AdherentMessageInterface && array_intersect(array_keys($event->getEntityChangeSet()), ['content', 'subject', 'filter'])) {
            foreach ($object->getMailchimpCampaigns() as $campaign) {
                $campaign->setSynchronized(false);
            }
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

    private function dispatchMessage(AdherentMessageInterface $object): void
    {
        $this->bus->dispatch(new AdherentMessageChangeCommand($object->getUuid()));
    }
}
