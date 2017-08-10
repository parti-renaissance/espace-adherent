<?php

namespace AppBundle\CitizenInitiative;

use AppBundle\Event\EventFactory;
use AppBundle\Events;
use AppBundle\Mailjet\MailjetService;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class CitizenInitiativeCommandHandler
{
    private $dispatcher;
    private $factory;
    private $manager;
    private $mailjet;

    public function __construct(
        EventDispatcherInterface $dispatcher,
        EventFactory $factory,
        ObjectManager $manager,
        MailjetService $mailjet
    ) {
        $this->dispatcher = $dispatcher;
        $this->manager = $manager;
        $this->factory = $factory;
        $this->mailjet = $mailjet;
    }

    public function handle(CitizenInitiativeCommand $command): void
    {
        $command->setCitizenInitiative($initiative = $this->factory->createFromCitizenInitiativeCommand($command));

        $this->manager->persist($initiative);
        $this->manager->flush();

        $this->dispatcher->dispatch(Events::CITIZEN_INITIATIVE_CREATED, new CitizenInitiativeCreatedEvent(
            $command->getAuthor(),
            $initiative
        ));
    }
}
