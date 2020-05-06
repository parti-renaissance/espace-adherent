<?php

namespace App\Event;

use App\Entity\Event;
use App\Events;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class EventRegistrationCommandHandler
{
    private $dispatcher;
    private $factory;
    private $manager;

    public function __construct(
        EventDispatcherInterface $dispatcher,
        EventRegistrationFactory $factory,
        EventRegistrationManager $manager
    ) {
        $this->dispatcher = $dispatcher;
        $this->factory = $factory;
        $this->manager = $manager;
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

        $this->dispatcher->dispatch(Events::EVENT_REGISTRATION_CREATED, new EventRegistrationEvent(
            $registration,
            $event->getSlug(),
            $sendMail
        ));

        if ($event instanceof Event) {
            $this->dispatcher->dispatch(Events::EVENT_UPDATED, new EventEvent(
                null,
                $event,
                $event->getCommittee()
            ));
        }
    }
}
