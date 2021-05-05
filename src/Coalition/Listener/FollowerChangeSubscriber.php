<?php

namespace App\Coalition\Listener;

use App\Entity\FollowerInterface;
use App\Mailchimp\Synchronisation\Command\CoalitionMemberChangeCommand;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Events;
use Symfony\Component\Messenger\MessageBusInterface;

class FollowerChangeSubscriber implements EventSubscriber
{
    private $bus;

    public function __construct(MessageBusInterface $bus)
    {
        $this->bus = $bus;
    }

    public function getSubscribedEvents()
    {
        return [
            Events::postRemove,
            Events::postPersist,
        ];
    }

    public function postPersist(LifecycleEventArgs $args): void
    {
        $this->dispatchMessage($args);
    }

    public function postRemove(LifecycleEventArgs $args): void
    {
        $this->dispatchMessage($args);
    }

    private function dispatchMessage(LifecycleEventArgs $args): void
    {
        $object = $args->getObject();

        if ($object instanceof FollowerInterface) {
            $this->bus->dispatch(
                new CoalitionMemberChangeCommand(
                    $object->isAdherent() ? $object->getAdherent()->getEmailAddress() : $object->getEmailAddress(),
                    $object->isAdherent()
                ));
        }
    }
}
