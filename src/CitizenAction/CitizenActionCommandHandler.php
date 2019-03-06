<?php

namespace AppBundle\CitizenAction;

use AppBundle\Entity\CitizenAction;
use AppBundle\Event\EventFactory;
use AppBundle\Events;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class CitizenActionCommandHandler
{
    private $dispatcher;
    private $factory;
    private $manager;

    public function __construct(EventDispatcherInterface $dispatcher, EventFactory $factory, ObjectManager $manager)
    {
        $this->dispatcher = $dispatcher;
        $this->manager = $manager;
        $this->factory = $factory;
    }

    public function handle(CitizenActionCommand $command): CitizenAction
    {
        $action = $this->factory->createFromCitizenActionCommand($command);

        $this->manager->persist($action);
        $this->manager->flush();

        $this->dispatcher->dispatch(Events::CITIZEN_ACTION_CREATED, new CitizenActionEvent($action));

        return $action;
    }

    public function handleUpdate(CitizenActionCommand $command, CitizenAction $action): CitizenAction
    {
        $this->factory->updateFromCitizenActionCommand($command, $action);

        $this->manager->flush();

        $this->dispatcher->dispatch(Events::CITIZEN_ACTION_UPDATED, new CitizenActionEvent($action));

        return $action;
    }
}
