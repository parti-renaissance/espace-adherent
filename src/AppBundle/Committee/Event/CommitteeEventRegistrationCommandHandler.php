<?php

namespace AppBundle\Committee\Event;

class CommitteeEventRegistrationCommandHandler
{
    private $factory;
    private $manager;

    public function __construct(
        CommitteeEventRegistrationFactory $factory,
        CommitteeEventRegistrationManager $manager
    ) {
        $this->factory = $factory;
        $this->manager = $manager;
    }

    public function handle(CommitteeEventRegistrationCommand $command)
    {
        $registration = $this->manager->searchRegistration(
            $command->getCommitteeEvent(),
            $command->getEmailAddress(),
            $command->getAdherent()
        );

        // Remove and replace an existing registration for this event
        if ($registration) {
            $this->manager->remove($registration);
        }

        $this->manager->create($this->factory->createFromCommand($command));
    }
}
