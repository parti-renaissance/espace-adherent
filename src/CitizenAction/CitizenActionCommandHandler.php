<?php

namespace App\CitizenAction;

use App\Entity\Event\CitizenAction;
use App\Event\EventFactory;
use App\Events;
use Doctrine\ORM\EntityManagerInterface as ObjectManager;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

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

        $this->dispatcher->dispatch(new CitizenActionEvent($action), Events::CITIZEN_ACTION_CREATED);

        return $action;
    }

    public function handleUpdate(CitizenActionCommand $command, CitizenAction $action): CitizenAction
    {
        $this->dispatcher->dispatch(new CitizenActionEvent($action), Events::CITIZEN_ACTION_PRE_UPDATE);

        $this->factory->updateFromCitizenActionCommand($command, $action);

        $this->manager->flush();

        $this->dispatcher->dispatch(new CitizenActionEvent($action), Events::CITIZEN_ACTION_UPDATED);

        return $action;
    }
}
