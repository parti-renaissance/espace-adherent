<?php

namespace AppBundle\Event;

use AppBundle\Events;
use AppBundle\Entity\CommitteeFeedItem;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class EventCommandHandler
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

    public function handle(EventCommand $command)
    {
        $command->setEvent($event = $this->factory->createFromEventCommand($command));

        $this->manager->persist($event);
        $this->manager->persist(CommitteeFeedItem::createEvent($event, $command->getAuthor()));
        $this->manager->flush();

        $this->dispatcher->dispatch(Events::EVENT_CREATED, new EventCreatedEvent(
            $command->getCommittee(),
            $command->getAuthor(),
            $event
        ));

        return $event;
    }
}
