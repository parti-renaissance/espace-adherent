<?php

namespace AppBundle\CitizenInitiative;

use AppBundle\Event\EventRegistrationCommand;
use AppBundle\Event\EventRegistrationFactory;
use AppBundle\Event\EventRegistrationManager;
use AppBundle\Mailjet\MailjetService;
use AppBundle\Mailjet\Message\CitizenInitiativeRegistrationConfirmationMessage;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class CitizenInitiativeRegistrationCommandHandler
{
    private $factory;
    private $manager;
    private $activitySubscriptionManager;
    private $mailjet;
    private $urlGenerator;

    public function __construct(
        EventRegistrationFactory $factory,
        EventRegistrationManager $manager,
        ActivitySubscriptionManager $activitySubscriptionManager,
        MailjetService $mailjet,
        UrlGeneratorInterface $urlGenerator
    ) {
        $this->factory = $factory;
        $this->manager = $manager;
        $this->activitySubscriptionManager = $activitySubscriptionManager;
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

        $citizenInitiativeLink = $this->generateUrl('app_citizen_initiative_show', [
            'slug' => $command->getEvent()->getSlug(),
        ]);

        $this->mailjet->sendMessage(CitizenInitiativeRegistrationConfirmationMessage::createFromRegistration($registration, $citizenInitiativeLink));

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
