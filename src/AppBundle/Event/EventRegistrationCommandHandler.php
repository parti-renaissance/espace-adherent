<?php

namespace AppBundle\Event;

class EventRegistrationCommandHandler
{
    private $factory;
    private $manager;

    public function __construct(
        EventRegistrationFactory $factory,
        EventRegistrationManager $manager
    ) {
        $this->factory = $factory;
        $this->manager = $manager;
    }

    public function handle(EventRegistrationCommand $command)
    {
        $registration = $this->manager->searchRegistration(
            $command->getEvent(),
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
