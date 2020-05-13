<?php

namespace App\ApplicationRequest\Listener;

use App\Entity\ApplicationRequest\ApplicationRequest;
use App\Mailchimp\Synchronisation\Command\AddApplicationRequestCandidateCommand;
use App\Mailchimp\Synchronisation\Command\RemoveApplicationRequestCandidateCommand;
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
            Events::postRemove,
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

    public function postRemove(LifecycleEventArgs $args): void
    {
        $object = $args->getObject();

        if ($object instanceof ApplicationRequest) {
            $this->bus->dispatch(new RemoveApplicationRequestCandidateCommand($object->getEmailAddress()));
        }
    }

    private function dispatchMessage(ApplicationRequest $object): void
    {
        $this->bus->dispatch(new AddApplicationRequestCandidateCommand($object->getId(), \get_class($object)));
    }
}
