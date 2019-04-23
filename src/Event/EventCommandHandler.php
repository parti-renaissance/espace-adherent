<?php

namespace AppBundle\Event;

use AppBundle\Entity\CommitteeFeedItem;
use AppBundle\Entity\Event;
use AppBundle\Events;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

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

        if ($event->getCommittee()) {
            $this->manager->persist(CommitteeFeedItem::createEvent($event, $command->getAuthor()));
        }

        $this->manager->flush();

        $this->dispatcher->dispatch(Events::EVENT_CREATED, new EventEvent(
            $command->getAuthor(),
            $event,
            $command->getCommittee()
        ));

        return $event;
    }

    public function handleUpdate(Event $event, EventCommand $command)
    {
        $this->factory->updateFromEventCommand($event, $command);

        $this->manager->flush();

        $this->dispatcher->dispatch(Events::EVENT_UPDATED, new EventEvent(
            $command->getAuthor(),
            $event,
            $command->getCommittee()
        ));

        return $event;
    }
}
