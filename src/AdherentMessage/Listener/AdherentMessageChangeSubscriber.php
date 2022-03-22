<?php

namespace App\AdherentMessage\Listener;

use App\AdherentMessage\Command\AdherentMessageChangeCommand;
use App\AdherentMessage\Command\AdherentMessageDeleteCommand;
use App\Entity\AdherentMessage\CampaignAdherentMessageInterface;
use App\Entity\AdherentMessage\Filter\CampaignAdherentMessageFilterInterface;
use App\Entity\AdherentMessage\MailchimpCampaign;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Events;
use Symfony\Component\Messenger\MessageBusInterface;

class AdherentMessageChangeSubscriber implements EventSubscriber
{
    private MessageBusInterface $bus;
    private array $objects = [];

    public function __construct(MessageBusInterface $bus)
    {
        $this->bus = $bus;
    }

    public function getSubscribedEvents()
    {
        return [
            Events::postPersist,
            Events::onFlush,
            Events::postFlush,
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

        if ($object instanceof CampaignAdherentMessageInterface) {
            $this->objects[] = $object;
        }
    }

    public function postUpdate(LifecycleEventArgs $args): void
    {
        $object = $args->getObject();

        if ($object instanceof CampaignAdherentMessageInterface && false === $object->isSynchronized()) {
            $this->objects[] = $object;
        } elseif ($object instanceof CampaignAdherentMessageFilterInterface && false === $object->isSynchronized()) {
            $this->objects[] = $object->getMessage();
        }
    }

    public function onFlush(OnFlushEventArgs $args): void
    {
        $em = $args->getEntityManager();
        $uow = $em->getUnitOfWork();

        foreach ($uow->getScheduledEntityUpdates() as $object) {
            if (!$object instanceof CampaignAdherentMessageFilterInterface) {
                continue;
            }

            $changeSet = array_keys($uow->getEntityChangeSet($object));

            if ($changeSet !== ['synchronized']) {
                $object->setSynchronized(false);
            }
        }
    }

    public function postFlush(): void
    {
        foreach ($this->objects as $object) {
            $this->dispatchMessage($object);
        }
    }

    private function dispatchMessage(CampaignAdherentMessageInterface $object): void
    {
        $this->bus->dispatch(new AdherentMessageChangeCommand($object->getUuid()));
    }
}
