<?php

namespace App\Event;

use App\Entity\CommitteeFeedItem;
use App\Entity\Event;
use App\Events;
use Doctrine\Common\Persistence\ObjectManager;
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

    public function handle(EventCommand $command, string $eventClass = Event::class): Event
    {
        $event = $this->factory->createFromEventCommand($command, $eventClass);

        $this->manager->persist($event);

        if ($event->getCommittee()) {
            $this->manager->persist(CommitteeFeedItem::createEvent($event, $command->getAuthor()));
        }

        $this->manager->flush();

        $this->dispatcher->dispatch(new EventEvent(
            $command->getAuthor(),
            $event,
            $command->getCommittee()
        ), Events::EVENT_CREATED);

        return $event;
    }

    public function handleUpdate(Event $event, EventCommand $command)
    {
        $this->dispatcher->dispatch(new EventEvent($command->getAuthor(), $event), Events::EVENT_PRE_UPDATE);

        $this->factory->updateFromEventCommand($event, $command);

        $this->manager->flush();

        $this->dispatcher->dispatch(new EventEvent(
            $command->getAuthor(),
            $event,
            $command->getCommittee()
        ), Events::EVENT_UPDATED);

        return $event;
    }
}
