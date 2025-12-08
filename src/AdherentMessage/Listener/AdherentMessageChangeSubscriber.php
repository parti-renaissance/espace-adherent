<?php

declare(strict_types=1);

namespace App\AdherentMessage\Listener;

use App\AdherentMessage\Command\AdherentMessageChangeCommand;
use App\AdherentMessage\Command\AdherentMessageDeleteCommand;
use App\Entity\AdherentMessage\AdherentMessageInterface;
use App\Entity\AdherentMessage\Filter\CampaignAdherentMessageFilterInterface;
use App\Entity\AdherentMessage\MailchimpCampaign;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Events;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsDoctrineListener(Events::postPersist)]
#[AsDoctrineListener(Events::onFlush)]
#[AsDoctrineListener(Events::postFlush)]
#[AsDoctrineListener(Events::postUpdate)]
#[AsDoctrineListener(Events::postRemove)]
class AdherentMessageChangeSubscriber
{
    private array $objects = [];

    public function __construct(private readonly MessageBusInterface $bus)
    {
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
            $this->objects[] = $object;
        }
    }

    public function postUpdate(LifecycleEventArgs $args): void
    {
        $object = $args->getObject();

        if ($object instanceof AdherentMessageInterface && false === $object->isSynchronized()) {
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

    private function dispatchMessage(AdherentMessageInterface $object): void
    {
        $this->bus->dispatch(new AdherentMessageChangeCommand($object->getUuid()));
    }
}
