<?php

namespace App\Event;

use App\Events;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class EventRegistrationCommandHandler
{
    public function __construct(
        private readonly EventDispatcherInterface $dispatcher,
        private readonly EventRegistrationFactory $factory,
        private readonly EventRegistrationManager $manager,
    ) {
    }

    public function handle(EventRegistrationCommand $command, bool $sendMail = true): void
    {
        $event = $command->getEvent();

        $registration = $this->manager->searchRegistration(
            $event,
            $command->getEmailAddress(),
            $command->getAdherent()
        );

        // Remove and replace an existing registration for this event
        if ($registration) {
            $this->manager->remove($registration);
        }

        $this->manager->create($registration = $this->factory->createFromCommand($command));

        $this->dispatcher->dispatch(new EventRegistrationEvent($registration, $sendMail), Events::EVENT_REGISTRATION_CREATED);
    }
}
