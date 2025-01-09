<?php

namespace App\Event;

use App\Entity\Event\BaseEvent;
use App\Entity\Event\CommitteeEvent;
use App\Events;
use Doctrine\ORM\EntityManagerInterface as ObjectManager;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class EventCommandHandler
{
    private $dispatcher;
    private $factory;
    private $manager;

    public function __construct(EventDispatcherInterface $dispatcher, EventFactory $factory, ObjectManager $manager)
    {
        $this->dispatcher = $dispatcher;
        $this->factory = $factory;
        $this->manager = $manager;
    }

    public function handle(EventCommand $command, string $eventClass = CommitteeEvent::class): BaseEvent
    {
        $event = $this->factory->createFromEventCommand($command, $eventClass);

        $this->manager->persist($event);

        if ($event instanceof CommitteeEvent) {
            $sfEvent = new CommitteeEventEvent($command->getAuthor(), $event, $command->getCommittee());
        } else {
            $sfEvent = new EventEvent($command->getAuthor(), $event);
        }

        $this->manager->flush();

        $this->dispatcher->dispatch($sfEvent, Events::EVENT_CREATED);

        return $event;
    }

    public function handleUpdate(BaseEvent $event, EventCommand $command): BaseEvent
    {
        if ($event instanceof CommitteeEvent) {
            $sfEvent = new CommitteeEventEvent($command->getAuthor(), $event, $command->getCommittee());
        } else {
            $sfEvent = new EventEvent($command->getAuthor(), $event);
        }

        $this->dispatcher->dispatch($sfEvent, Events::EVENT_PRE_UPDATE);

        $this->factory->updateFromEventCommand($event, $command);

        $this->manager->flush();

        $this->dispatcher->dispatch($sfEvent, Events::EVENT_UPDATED);

        return $event;
    }
}
