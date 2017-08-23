<?php

namespace AppBundle\CitizenInitiative;

use AppBundle\Entity\CitizenInitiative;
use AppBundle\Events;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class CitizenInitiativeManager
{
    private $manager;
    private $dispatcher;

    public function __construct(ObjectManager $manager, EventDispatcherInterface $dispatcher)
    {
        $this->manager = $manager;
        $this->dispatcher = $dispatcher;
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
}
