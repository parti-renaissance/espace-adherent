<?php

namespace AppBundle\CitizenInitiative;

use AppBundle\Event\EventFactory;
use AppBundle\Mailjet\MailjetService;
use Doctrine\Common\Persistence\ObjectManager;

class CitizenInitiativeCommandHandler
{
    private $factory;
    private $manager;
    private $mailjet;

    public function __construct(
        EventFactory $factory,
        ObjectManager $manager,
        MailjetService $mailjet
    ) {
        $this->manager = $manager;
        $this->factory = $factory;
        $this->mailjet = $mailjet;
    }

    public function handle(CitizenInitiativeCommand $command): void
    {
        $command->setCitizenInitiative($initiative = $this->factory->createFromCitizenInitiativeCommand($command));

        $this->manager->persist($initiative);
        $this->manager->flush();
    }
}
