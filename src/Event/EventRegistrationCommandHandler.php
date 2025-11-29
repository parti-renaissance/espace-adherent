<?php

declare(strict_types=1);

namespace App\Event;

use App\Entity\Event\RegistrationStatusEnum;
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

    public function handle(EventRegistrationCommand $command, bool $sendMail = true): bool
    {
        $event = $command->getEvent();

        $registration = $this->manager->searchRegistration(
            $event,
            $command->getEmailAddress(),
            $command->getAdherent()
        );

        if (RegistrationStatusEnum::INVITED === $command->status && $registration) {
            return false;
        }

        // Remove and replace an existing registration for this event
        if ($registration) {
            $this->manager->remove($registration);
        }

        $this->manager->create($registration = $this->factory->createFromCommand($command));

        $this->dispatcher->dispatch(new EventRegistrationEvent($registration, $sendMail), Events::EVENT_REGISTRATION_CREATED);

        return true;
    }
}
