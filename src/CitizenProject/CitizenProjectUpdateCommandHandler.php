<?php

namespace AppBundle\CitizenProject;

use AppBundle\Address\PostAddressFactory;
use AppBundle\Events;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class CitizenProjectUpdateCommandHandler
{
    private $dispatcher;
    private $addressFactory;
    private $manager;

    public function __construct(
        EventDispatcherInterface $dispatcher,
        ObjectManager $manager,
        PostAddressFactory $addressFactory
    ) {
        $this->dispatcher = $dispatcher;
        $this->manager = $manager;
        $this->addressFactory = $addressFactory;
    }

    public function handle(CitizenProjectCommand $command)
    {
        if (!$citizenProject = $command->getCitizenProject()) {
            throw new \RuntimeException('A CitizenProject instance is required.');
        }

        $citizenProject->update(
            $command->name,
            $command->subtitle,
            $command->category,
            $command->assistanceNeeded,
            $command->problemDescription,
            $command->proposedSolution,
            $command->requiredMeans,
            $this->addressFactory->createFromNullableAddress($command->getAddress())
        );

        $this->manager->persist($citizenProject);
        $this->manager->flush();

        $this->dispatcher->dispatch(Events::CITIZEN_PROJECT_UPDATED, new CitizenProjectWasUpdatedEvent($citizenProject));
    }
}
