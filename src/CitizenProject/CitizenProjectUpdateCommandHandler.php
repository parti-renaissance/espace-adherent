<?php

namespace App\CitizenProject;

use App\Address\PostAddressFactory;
use App\Entity\CitizenProject;
use App\Events;
use App\Referent\ReferentTagManager;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class CitizenProjectUpdateCommandHandler
{
    private $dispatcher;
    private $addressFactory;
    private $manager;
    private $citizenProjectManager;
    private $referentTagManager;

    public function __construct(
        EventDispatcherInterface $dispatcher,
        ObjectManager $manager,
        PostAddressFactory $addressFactory,
        CitizenProjectManager $citizenProjectManager,
        ReferentTagManager $referentTagManager
    ) {
        $this->dispatcher = $dispatcher;
        $this->manager = $manager;
        $this->addressFactory = $addressFactory;
        $this->citizenProjectManager = $citizenProjectManager;
        $this->referentTagManager = $referentTagManager;
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
            $command->problemDescription,
            $command->proposedSolution,
            $command->requiredMeans,
            $this->addressFactory->createFromNullableAddress($command->getAddress()),
            $command->phone,
            $command->getSkills(),
            $command->getCommittees(),
            $command->getImage(),
            $command->getDistrict()
        );

        $this->doUpdateImage($command, $citizenProject);

        $this->referentTagManager->assignReferentLocalTags($citizenProject);

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
