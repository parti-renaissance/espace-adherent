<?php

namespace App\AdherentMessage\Listener;

use App\AdherentMessage\Command\AdherentMessageChangeCommand;
use App\AdherentMessage\Command\AdherentMessageDeleteCommand;
use App\Entity\AdherentMessage\CampaignAdherentMessageInterface;
use App\Entity\AdherentMessage\Filter\CampaignAdherentMessageFilterInterface;
use App\Entity\AdherentMessage\MailchimpCampaign;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Event\PostPersistEventArgs;
use Doctrine\ORM\Event\PostRemoveEventArgs;
use Doctrine\ORM\Event\PostUpdateEventArgs;
use Doctrine\ORM\Events;
use Symfony\Component\Messenger\MessageBusInterface;

class AdherentMessageChangeSubscriber implements EventSubscriber
{
    private array $objects = [];

    public function __construct(private readonly MessageBusInterface $bus)
    {
    }

    public function getSubscribedEvents(): array
    {
        return [
            Events::postPersist,
            Events::onFlush,
            Events::postFlush,
            Events::postUpdate,
            Events::postRemove,
        ];
    }

    public function postRemove(PostRemoveEventArgs $args): void
    {
        $object = $args->getObject();

        if ($object instanceof MailchimpCampaign && $object->getExternalId()) {
            $this->bus->dispatch(new AdherentMessageDeleteCommand($object->getExternalId()));
        }
    }

    public function postPersist(PostPersistEventArgs $args): void
    {
        $object = $args->getObject();

        if ($object instanceof CampaignAdherentMessageInterface) {
            $this->objects[] = $object;
        }
    }

    public function postUpdate(PostUpdateEventArgs $args): void
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
        $em = $args->getObjectManager();
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
        $this->objects = [];
    }

    private function dispatchMessage(CampaignAdherentMessageInterface $object): void
    {
        $this->bus->dispatch(new AdherentMessageChangeCommand($object->getUuid()));
    }
}
