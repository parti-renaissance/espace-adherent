<?php

namespace App\InstitutionalEvent;

use App\Entity\Event\InstitutionalEvent;
use App\Event\EventFactory;
use App\Events;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

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

        $this->dispatcher->dispatch(new InstitutionalEventEvent($institutionalEvent), Events::INSTITUTIONAL_EVENT_CREATED);

        return $institutionalEvent;
    }

    public function handleUpdate(
        InstitutionalEventCommand $command,
        InstitutionalEvent $institutionalEvent
    ): InstitutionalEvent {
        $this->factory->updateFromInstitutionalEventCommand($command, $institutionalEvent);

        $this->entityManager->flush();

        $this->dispatcher->dispatch(new InstitutionalEventEvent($institutionalEvent), Events::INSTITUTIONAL_EVENT_UPDATED);

        return $institutionalEvent;
    }

    public function handleDelete(InstitutionalEvent $institutionalEvent): InstitutionalEvent
    {
        $this->entityManager->remove($institutionalEvent);
        $this->entityManager->flush();

        $this->dispatcher->dispatch(new InstitutionalEventEvent($institutionalEvent), Events::INSTITUTIONAL_EVENT_DELETED);

        return $institutionalEvent;
    }
}
