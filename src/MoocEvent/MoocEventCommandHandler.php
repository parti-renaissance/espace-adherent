<?php

namespace AppBundle\MoocEvent;

use AppBundle\Entity\GroupFeedItem;
use AppBundle\Entity\MoocEvent;
use AppBundle\Events;
use AppBundle\MoocEvent\MoocEventCancelledEvent;
use AppBundle\MoocEvent\MoocEventCommand;
use AppBundle\MoocEvent\MoocEventCreatedEvent;
use AppBundle\MoocEvent\MoocEventUpdatedEvent;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class MoocEventCommandHandler
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

    public function handle(MoocEventCommand $command)
    {
        $command->setMoocEvent($moocEvent = $this->factory->createFromMoocEventCommand($command));

        $this->manager->persist($moocEvent);

        if ($moocEvent->getGroup()) {
            $this->manager->persist(GroupFeedItem::createMoocEvent($moocEvent, $command->getAuthor()));
        }

        $this->manager->flush();

        $this->dispatcher->dispatch(Events::MOOC_EVENT_CREATED, new MoocEventCreatedEvent(
            $command->getAuthor(),
            $event,
            $command->getGroup()
        ));

        return $event;
    }

    public function handleUpdate(MoocEvent $moocEvent, MoocEventCommand $command)
    {
        $this->factory->updateFromMoocEventCommand($moocEvent, $command);

        $this->manager->flush();

        $this->dispatcher->dispatch(Events::MOOC_EVENT_UPDATED, new MoocEventUpdatedEvent(
            $command->getAuthor(),
            $moocEvent,
            $command->getGroup()
        ));

        return $moocEvent;
    }

    public function handleCancel(MoocEvent $moocEvent, MoocEventCommand $command)
    {
        $moocEvent->cancel();

        $this->manager->flush();

        $this->dispatcher->dispatch(Events::EVENT_CANCELLED, new MoocEventCancelledEvent(
            $command->getAuthor(),
            $moocEvent,
            $command->getGroup()
        ));

        return $moocEvent;
    }
}
