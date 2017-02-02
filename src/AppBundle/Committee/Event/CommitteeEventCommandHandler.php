<?php

namespace AppBundle\Committee\Event;

use AppBundle\Committee\CommitteeEvents;
use AppBundle\Entity\CommitteeFeedItem;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class CommitteeEventCommandHandler
{
    private $dispatcher;
    private $factory;
    private $manager;

    public function __construct(
        EventDispatcherInterface $dispatcher,
        CommitteeEventFactory $factory,
        ObjectManager $manager
    ) {
        $this->dispatcher = $dispatcher;
        $this->factory = $factory;
        $this->manager = $manager;
    }

    public function handle(CommitteeEventCommand $command)
    {
        $command->setCommitteeEvent($event = $this->factory->createFromCommitteeEventCommand($command));

        $this->manager->persist($event);
        $this->manager->persist(CommitteeFeedItem::createEvent($event, $command->getAuthor()));
        $this->manager->flush();

        $this->dispatcher->dispatch(CommitteeEvents::EVENT_CREATED, new CommitteeEventCreatedEvent(
            $command->getCommittee(),
            $command->getAuthor(),
            $event
        ));
    }
}
