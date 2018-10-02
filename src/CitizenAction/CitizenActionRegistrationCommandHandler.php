<?php

namespace AppBundle\CitizenAction;

use AppBundle\Event\EventRegistrationCommand;
use AppBundle\Event\EventRegistrationFactory;
use AppBundle\Event\EventRegistrationManager;
use AppBundle\Mail\Transactional\CitizenActionRegistrationConfirmationMail;
use EnMarche\MailerBundle\MailPost\MailPostInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class CitizenActionRegistrationCommandHandler
{
    private $factory;
    private $manager;
    private $mailPost;
    private $urlGenerator;

    public function __construct(
        EventRegistrationFactory $factory,
        EventRegistrationManager $manager,
        MailPostInterface $mailPost,
        UrlGeneratorInterface $urlGenerator
    ) {
        $this->factory = $factory;
        $this->manager = $manager;
        $this->mailPost = $mailPost;
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

        $this->mailPost->address(
            CitizenActionRegistrationConfirmationMail::class,
            CitizenActionRegistrationConfirmationMail::createRecipient($registration),
            null,
            CitizenActionRegistrationConfirmationMail::createTemplateVars(
                $registration->getEvent(),
                $this->generateUrl('app_citizen_action_export_ical', [
                    'slug' => $command->getEvent()->getSlug(),
                ])
            )
        );
    }

    private function generateUrl(string $route, array $params = []): string
    {
        return $this->urlGenerator->generate($route, $params, UrlGeneratorInterface::ABSOLUTE_URL);
    }
}
