<?php

namespace AppBundle\CitizenInitiative;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\CitizenInitiative;
use AppBundle\Events;
use AppBundle\Repository\CitizenInitiativeRepository;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class CitizenInitiativeManager
{
    private $manager;
    private $dispatcher;
    private $repository;

    public function __construct(ObjectManager $manager, EventDispatcherInterface $dispatcher, CitizenInitiativeRepository $repository)
    {
        $this->manager = $manager;
        $this->dispatcher = $dispatcher;
        $this->repository = $repository;
    }

    public function updateCitizenInitiative(CitizenInitiative $initiative): void
    {
        if (!$initiative->getId()) {
            $this->manager->persist($initiative);
        }

        $this->manager->flush();
    }

    public function changeExpertStatusCitizenInitiative(CitizenInitiative $initiative): void
    {
        $initiative->setExpertFound(!$initiative->isExpertFound());

        $this->manager->flush();
    }

    public function publishCitizenInitiative(CitizenInitiative $initiative): void
    {
        $initiative->publish();

        $this->checkPublicationCitizenInitiative($initiative, false);

        $this->manager->flush();
    }

    public function checkPublicationCitizenInitiative(CitizenInitiative $initiative, bool $flush = true): void
    {
        if ($initiative->isPublished() && !$initiative->wasPublished()) {
            $initiative->setWasPublished(true);

            if ($flush) {
                $this->manager->flush();
            }

            $this->dispatcher->dispatch(Events::CITIZEN_INITIATIVE_VALIDATED, new CitizenInitiativeValidatedEvent(
                $initiative
            ));
        }
    }

    public function removeOrganizerCitizenInitiatives(Adherent $adherent): void
    {
        $this->repository->anonymizeOrganizerCitizenInitiatives($adherent, CitizenInitiativeRepository::TYPE_PAST);
        $this->repository->removeOrganizerCitizenInitiatives($adherent, CitizenInitiativeRepository::TYPE_UPCOMING);
    }
}
