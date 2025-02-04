<?php

namespace App\Event;

use App\Entity\Event\Event;
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

    public function handle(EventCommand $command): Event
    {
        $event = $this->factory->createFromEventCommand($command);

        $this->manager->persist($event);

        $this->manager->flush();

        $this->dispatcher->dispatch(new EventEvent($command->getAuthor(), $event), Events::EVENT_CREATED);

        return $event;
    }

    public function handleUpdate(Event $event, EventCommand $command): Event
    {
        $sfEvent = new EventEvent($command->getAuthor(), $event);

        $this->dispatcher->dispatch($sfEvent, Events::EVENT_PRE_UPDATE);

        $this->factory->updateFromEventCommand($event, $command);

        $this->manager->flush();

        $this->dispatcher->dispatch($sfEvent, Events::EVENT_UPDATED);

        return $event;
    }
}
