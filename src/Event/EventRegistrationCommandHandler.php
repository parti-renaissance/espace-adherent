<?php

namespace AppBundle\Event;

use AppBundle\Entity\CitizenAction;
use AppBundle\Mailer\MailerService;
use AppBundle\Mailer\Message\CitizenActionRegistrationConfirmationMessage;
use AppBundle\Mailer\Message\EventRegistrationConfirmationMessage;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class EventRegistrationCommandHandler
{
    private $factory;
    private $manager;
    private $mailer;
    private $urlGenerator;

    public function __construct(
        EventRegistrationFactory $factory,
        EventRegistrationManager $manager,
        MailerService $mailer,
        UrlGeneratorInterface $urlGenerator
    ) {
        $this->factory = $factory;
        $this->manager = $manager;
        $this->mailer = $mailer;
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

        if ($command->getEvent() instanceof CitizenAction) {
            $calendarExportLink = $this->generateUrl('app_citizen_action_export_ical', [
                'slug' => $command->getEvent()->getSlug(),
            ]);

            $message = CitizenActionRegistrationConfirmationMessage::createFromRegistration($registration, $calendarExportLink);
        } else {
            $eventLink = $this->generateUrl('app_event_show', [
                'slug' => $command->getEvent()->getSlug(),
            ]);

            $message = EventRegistrationConfirmationMessage::createFromRegistration($registration, $eventLink);
        }

        $this->mailer->sendMessage($message);
    }

    private function generateUrl(string $route, array $params = []): string
    {
        return $this->urlGenerator->generate($route, $params, UrlGeneratorInterface::ABSOLUTE_URL);
    }
}
