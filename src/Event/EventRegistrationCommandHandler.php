<?php

namespace AppBundle\Event;

use AppBundle\Mailjet\MailjetService;
use AppBundle\Mailjet\Message\EventRegistrationConfirmationMessage;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class EventRegistrationCommandHandler
{
    private $factory;
    private $manager;
    private $mailjet;
    private $urlGenerator;

    public function __construct(
        EventRegistrationFactory $factory,
        EventRegistrationManager $manager,
        MailjetService $mailjet,
        UrlGeneratorInterface $urlGenerator
    ) {
        $this->factory = $factory;
        $this->manager = $manager;
        $this->mailjet = $mailjet;
        $this->urlGenerator = $urlGenerator;
    }

    public function handle(EventRegistrationCommand $command): void
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

        $eventLink = $this->generateUrl('app_event_show', [
            'slug' => $command->getEvent()->getSlug(),
        ]);

        $this->mailjet->sendMessage(EventRegistrationConfirmationMessage::createFromRegistration($registration, $eventLink));
    }

    private function generateUrl(string $route, array $params = []): string
    {
        return $this->urlGenerator->generate($route, $params, UrlGeneratorInterface::ABSOLUTE_URL);
    }
}
