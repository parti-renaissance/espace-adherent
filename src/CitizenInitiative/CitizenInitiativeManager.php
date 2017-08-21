<?php

namespace AppBundle\CitizenInitiative;

use AppBundle\Entity\CitizenInitiative;
use Doctrine\Common\Persistence\ObjectManager;

class CitizenInitiativeManager
{
    private $manager;

    public function __construct(ObjectManager $manager)
    {
        $this->manager = $manager;
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
}
