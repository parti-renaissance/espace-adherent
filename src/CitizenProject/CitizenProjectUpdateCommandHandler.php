<?php

namespace AppBundle\CitizenProject;

use AppBundle\Address\PostAddressFactory;
use AppBundle\Entity\CitizenProject;
use AppBundle\Events;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class CitizenProjectUpdateCommandHandler
{
    private $dispatcher;

    private $addressFactory;

    private $manager;

    private $citizenProjectManager;

    public function __construct(
        EventDispatcherInterface $dispatcher,
        ObjectManager $manager,
        PostAddressFactory $addressFactory,
        CitizenProjectManager $citizenProjectManager
    ) {
        $this->dispatcher = $dispatcher;
        $this->manager = $manager;
        $this->addressFactory = $addressFactory;
        $this->citizenProjectManager = $citizenProjectManager;
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
            $command->assistanceContent,
            $command->problemDescription,
            $command->proposedSolution,
            $command->requiredMeans,
            $this->addressFactory->createFromNullableAddress($command->getAddress()),
            $command->phone,
            $command->getSkills(),
            $command->getCommittees(),
            $command->getImage()
        );

        $this->doUpdateImage($command, $citizenProject);

        $this->manager->persist($citizenProject);
        $this->manager->flush();

        $this->dispatcher->dispatch(Events::CITIZEN_PROJECT_UPDATED, new CitizenProjectWasUpdatedEvent($citizenProject));
    }

    private function doUpdateImage(CitizenProjectCommand $command, CitizenProject $citizenProject): void
    {
        // Uploads an image
        if (null !== $command->getImage()) {
            $this->citizenProjectManager->addImage($citizenProject);
        }

        // Removes an image
        if ($command->isRemoveImage() && $citizenProject->hasImageUploaded()) {
            $this->citizenProjectManager->removeImage($citizenProject);
        }
    }
}
