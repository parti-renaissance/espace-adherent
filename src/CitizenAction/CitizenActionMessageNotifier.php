<?php

namespace AppBundle\CitizenAction;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\CitizenAction;
use AppBundle\Entity\EventRegistration;
use AppBundle\Events;
use AppBundle\Mailer\MailerService;
use AppBundle\Mailer\Message\CitizenActionCancellationMessage;
use AppBundle\Repository\EventRegistrationRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class CitizenActionMessageNotifier implements EventSubscriberInterface
{
    private $mailer;
    private $urlGenerator;
    private $registrationRepository;

    public function __construct(
        MailerService $mailer,
        UrlGeneratorInterface $urlGenerator,
        EventRegistrationRepository $registrationRepository
    ) {
        $this->mailer = $mailer;
        $this->urlGenerator = $urlGenerator;
        $this->registrationRepository = $registrationRepository;
    }

    public function sendCancellationEmail(CitizenActionEvent $citizenActionEvent): void
    {
        $citizenAction = $citizenActionEvent->getCitizenAction();
        if (!$citizenAction->isCancelled()) {
            return;
        }

        $subscriptions = $this->registrationRepository->findByEvent($citizenAction);

        if (count($subscriptions) > 0) {
            $registrationChunks = array_chunk($subscriptions->toArray(), MailerService::PAYLOAD_MAXSIZE);

            foreach ($registrationChunks as $chunk) {
                $this->mailer->sendMessage($this->createCancelMessage(
                    $chunk,
                    $citizenAction,
                    $citizenActionEvent->getAuthor()
                ));
            }
        }
    }

    public static function getSubscribedEvents()
    {
        return [
            Events::CITIZEN_ACTION_CANCELLED => ['sendCancellationEmail'],
        ];
    }

    private function createCancelMessage(array $registered, CitizenAction $citizenAction, Adherent $host): CitizenActionCancellationMessage
    {
        return CitizenActionCancellationMessage::create(
            $registered,
            $host,
            $citizenAction,
            $this->urlGenerator->generate('app_search_events', [], UrlGeneratorInterface::ABSOLUTE_URL),
            function (EventRegistration $registration) {
                return CitizenActionCancellationMessage::getRecipientVars($registration->getFirstName());
            }
        );
    }
}
