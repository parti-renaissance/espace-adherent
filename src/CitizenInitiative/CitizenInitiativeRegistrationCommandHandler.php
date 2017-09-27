<?php

namespace AppBundle\CitizenInitiative;

use AppBundle\Event\EventRegistrationCommand;
use AppBundle\Event\EventRegistrationFactory;
use AppBundle\Event\EventRegistrationManager;
use AppBundle\Mailer\MailerService;
use AppBundle\Mailer\Message\CitizenInitiativeRegistrationConfirmationMessage;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class CitizenInitiativeRegistrationCommandHandler
{
    private $factory;
    private $manager;
    private $activitySubscriptionManager;
    private $mailer;
    private $urlGenerator;

    public function __construct(
        EventRegistrationFactory $factory,
        EventRegistrationManager $manager,
        ActivitySubscriptionManager $activitySubscriptionManager,
        MailerService $mailer,
        UrlGeneratorInterface $urlGenerator
    ) {
        $this->factory = $factory;
        $this->manager = $manager;
        $this->activitySubscriptionManager = $activitySubscriptionManager;
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

        $citizenInitiativeLink = $this->generateUrl('app_citizen_initiative_show', [
            'uuid' => (string) $command->getEvent()->getUuid(),
            'slug' => $command->getEvent()->getSlug(),
        ]);

        $this->mailer->sendMessage(CitizenInitiativeRegistrationConfirmationMessage::createFromRegistration($registration, $citizenInitiativeLink));

        // Subscribe to citizen initiative organizator activity
        if ($adherent = $command->getAdherent()) {
            $this->activitySubscriptionManager->subscribeToAdherentActivity($adherent, $command->getEvent()->getOrganizer());
        }
    }

    private function generateUrl(string $route, array $params = []): string
    {
        return $this->urlGenerator->generate($route, $params, UrlGeneratorInterface::ABSOLUTE_URL);
    }
}
