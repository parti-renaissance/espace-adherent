<?php

namespace App\Event;

use App\Entity\Event\CommitteeEvent;
use App\Events;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

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

        $this->dispatcher->dispatch(new EventRegistrationEvent(
            $registration,
            $event->getSlug(),
            $sendMail
        ), Events::EVENT_REGISTRATION_CREATED);

        if ($event instanceof CommitteeEvent) {
            $this->dispatcher->dispatch(new EventEvent(
                null,
                $event,
                $event->getCommittee()
            ), Events::EVENT_UPDATED);
        }
    }
}
