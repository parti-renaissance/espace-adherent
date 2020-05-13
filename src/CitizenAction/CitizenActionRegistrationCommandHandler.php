<?php

namespace App\CitizenAction;

use App\Event\EventRegistrationCommand;
use App\Event\EventRegistrationFactory;
use App\Event\EventRegistrationManager;
use App\Mailer\MailerService;
use App\Mailer\Message\CitizenActionRegistrationConfirmationMessage;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class CitizenActionRegistrationCommandHandler
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

        $citizenActionCalendarLink = $this->generateUrl('app_citizen_action_export_ical', [
            'slug' => $command->getEvent()->getSlug(),
        ]);

        $this->mailer->sendMessage(CitizenActionRegistrationConfirmationMessage::createFromRegistration($registration, $citizenActionCalendarLink));
    }

    private function generateUrl(string $route, array $params = []): string
    {
        return $this->urlGenerator->generate($route, $params, UrlGeneratorInterface::ABSOLUTE_URL);
    }
}
