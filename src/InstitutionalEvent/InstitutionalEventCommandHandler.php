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
        $event = $this->factory->createFromInstitutionalEventCommand($command);

        $this->entityManager->persist($event);
        $this->entityManager->flush();

        $this->dispatcher->dispatch(
            Events::INSTITUTIONAL_EVENT_CREATED, new InstitutionalEventEvent($command->getAuthor(), $event)
        );

        return $event;
    }
}
