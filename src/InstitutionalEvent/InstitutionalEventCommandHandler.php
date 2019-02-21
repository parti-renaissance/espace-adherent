<?php

namespace AppBundle\InstitutionalEvent;

use AppBundle\Entity\InstitutionalEvent;
use AppBundle\Event\EventFactory;
use AppBundle\Events;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class InstitutionalEventCommandHandler
{
    private $dispatcher;
    private $entityManager;
    private $factory;

    public function __construct(
        EventDispatcherInterface $dispatcher,
        EntityManagerInterface $entityManager,
        EventFactory $factory
    ) {
        $this->dispatcher = $dispatcher;
        $this->factory = $factory;
        $this->entityManager = $entityManager;
    }

    public function handle(InstitutionalEventCommand $command): InstitutionalEvent
    {
        $institutionalEvent = $this->factory->createFromInstitutionalEventCommand($command);

        $this->entityManager->persist($institutionalEvent);
        $this->entityManager->flush();

        $this->dispatcher->dispatch(
            Events::INSTITUTIONAL_EVENT_CREATED, new InstitutionalEventEvent($institutionalEvent)
        );

        return $institutionalEvent;
    }

    public function handleUpdate(
        InstitutionalEventCommand $command,
        InstitutionalEvent $institutionalEvent
    ): InstitutionalEvent {
        $this->factory->updateFromInstitutionalEventCommand($command, $institutionalEvent);

        $this->entityManager->flush();

        $this->dispatcher->dispatch(
            Events::INSTITUTIONAL_EVENT_UPDATED, new InstitutionalEventEvent($institutionalEvent)
        );

        return $institutionalEvent;
    }

    public function handleDelete(InstitutionalEvent $institutionalEvent): InstitutionalEvent
    {
        $this->entityManager->remove($institutionalEvent);
        $this->entityManager->flush();

        $this->dispatcher->dispatch(
            Events::INSTITUTIONAL_EVENT_DELETED, new InstitutionalEventEvent($institutionalEvent)
        );

        return $institutionalEvent;
    }
}
