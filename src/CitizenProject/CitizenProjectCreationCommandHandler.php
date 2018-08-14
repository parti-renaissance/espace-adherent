<?php

namespace AppBundle\CitizenProject;

use AppBundle\Entity\TurnkeyProject;
use AppBundle\Events;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class CitizenProjectCreationCommandHandler
{
    private $dispatcher;

    private $factory;

    private $manager;

    private $citizenProjectManager;

    public function __construct(
        EventDispatcherInterface $dispatcher,
        CitizenProjectFactory $factory,
        ObjectManager $manager,
        CitizenProjectManager $citizenProjectManager
    ) {
        $this->dispatcher = $dispatcher;
        $this->factory = $factory;
        $this->manager = $manager;
        $this->citizenProjectManager = $citizenProjectManager;
    }

    public function handle(CitizenProjectCreationCommand $command, TurnkeyProject $turnkeyProject = null): void
    {
        $adherent = $command->getAdherent();
        $citizenProject = $this->factory->createFromCitizenProjectCreationCommand($command);

        // Uploads an image
        if (null !== $command->getImage()) {
            $this->citizenProjectManager->addImage($citizenProject);
        } elseif ($turnkeyProject) {
            $this->citizenProjectManager->copyImageFromTurnkeyProject($citizenProject, $turnkeyProject);
        }

        $command->setCitizenProject($citizenProject);

        $this->manager->persist($citizenProject);
        $this->manager->persist($adherent->followCitizenProject($citizenProject));
        $this->manager->flush();

        $this->dispatcher->dispatch(Events::CITIZEN_PROJECT_CREATED, new CitizenProjectWasCreatedEvent($citizenProject, $adherent));
    }
}
