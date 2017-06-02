<?php

namespace AppBundle\Event;

use AppBundle\Mailjet\MailjetService;
use AppBundle\Mailjet\Message\EventRegistrationConfirmationMessage;

class EventRegistrationCommandHandler
{
    private $factory;
    private $manager;
    private $mailjet;

    public function __construct(
        EventRegistrationFactory $factory,
        EventRegistrationManager $manager,
        MailjetService $mailjet
    ) {
        $this->factory = $factory;
        $this->manager = $manager;
        $this->mailjet = $mailjet;
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

        $this->manager->create($registration = $this->factory->createFromCommand($command));

        $this->mailjet->sendMessage(EventRegistrationConfirmationMessage::createFromRegistration($registration));
    }
}
