<?php

namespace AppBundle\InstitutionalEvent;

use AppBundle\Entity\InstitutionalEvent;
use AppBundle\Event\EventFactory;
use AppBundle\Events;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class InstitutionalEventCommandHandler
{
    private $dispatcher;
    private $factory;
    private $manager;

    public function __construct(
        EventDispatcherInterface $dispatcher,
        EventFactory $factory,
        ObjectManager $manager
    ) {
        $this->dispatcher = $dispatcher;
        $this->factory = $factory;
        $this->manager = $manager;
    }

    public function handle(InstitutionalEventCommand $command): InstitutionalEvent
    {
        $event = $this->factory->createFromInstitutionalEventCommand($command);

        $this->manager->persist($event);
        $this->manager->flush();

        $this->dispatcher->dispatch(
            Events::INSTITUTIONAL_EVENT_CREATED, new InstitutionalEventEvent($command->getAuthor(), $event)
        );

        return $event;
    }
}
