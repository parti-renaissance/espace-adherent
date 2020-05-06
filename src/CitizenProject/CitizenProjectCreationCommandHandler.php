<?php

namespace App\CitizenProject;

use App\Entity\TurnkeyProject;
use App\Events;
use App\Referent\ReferentTagManager;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class CitizenProjectCreationCommandHandler
{
    private $dispatcher;
    private $factory;
    private $manager;
    private $citizenProjectManager;
    private $referentTagManager;

    public function __construct(
        EventDispatcherInterface $dispatcher,
        CitizenProjectFactory $factory,
        ObjectManager $manager,
        CitizenProjectManager $citizenProjectManager,
        ReferentTagManager $referentTagManager
    ) {
        $this->dispatcher = $dispatcher;
        $this->factory = $factory;
        $this->manager = $manager;
        $this->citizenProjectManager = $citizenProjectManager;
        $this->referentTagManager = $referentTagManager;
    }

    public function handle(CitizenProjectCreationCommand $command): void
    {
        $adherent = $command->getAdherent();
        $citizenProject = $this->factory->createFromCitizenProjectCreationCommand($command);
        $turnkeyProject = $citizenProject->getTurnkeyProject();

        /* District is not required but if CitizenProject is created from TurnkeyProject
        and if the district is not set, it should be equal to the city name. */
        if ($turnkeyProject && !$citizenProject->getDistrict() && $citizenProject->getCityName()) {
            $citizenProject->setDistrict($citizenProject->getCityName());
        }

        // Uploads an image
        if (null !== $command->getImage()) {
            $this->citizenProjectManager->addImage($citizenProject);
        } elseif ($turnkeyProject && $turnkeyProject->getImageName()) {
            $this->citizenProjectManager->copyImageFromTurnkeyProject($citizenProject, $turnkeyProject);
        } else {
            $this->citizenProjectManager->setDefaultImage($citizenProject);
        }

        $this->referentTagManager->assignReferentLocalTags($citizenProject);

        $command->setCitizenProject($citizenProject);

        $this->manager->persist($citizenProject);
        $this->manager->persist($adherent->followCitizenProject($citizenProject));
        $this->manager->flush();

        $this->dispatcher->dispatch(Events::CITIZEN_PROJECT_CREATED, new CitizenProjectWasCreatedEvent($citizenProject, $adherent));
    }
}
