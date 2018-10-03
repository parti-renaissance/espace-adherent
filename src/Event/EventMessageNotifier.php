<?php

namespace AppBundle\Event;

use AppBundle\Events;
use AppBundle\Committee\CommitteeManager;
use AppBundle\Mail\Transactional\EventCancellationMail;
use AppBundle\Mail\Transactional\EventNotificationMail;
use AppBundle\Repository\EventRegistrationRepository;
use EnMarche\MailerBundle\MailPost\MailPostInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class EventMessageNotifier implements EventSubscriberInterface
{
    private $mailPost;
    private $committeeManager;
    private $registrationRepository;
    private $urlGenerator;

    public function __construct(
        MailPostInterface $mailPost,
        CommitteeManager $committeeManager,
        EventRegistrationRepository $registrationRepository,
        UrlGeneratorInterface $urlGenerator
    ) {
        $this->mailPost = $mailPost;
        $this->committeeManager = $committeeManager;
        $this->registrationRepository = $registrationRepository;
        $this->urlGenerator = $urlGenerator;
    }

    public function onEventCreated(EventEvent $event): void
    {
        if (!$committee = $event->getCommittee()) {
            return;
        }

        $followers = $this->committeeManager->getOptinCommitteeFollowers($committee);
        $committeeEvent = $event->getEvent();
        $host = $event->getAuthor();

        $this->mailPost->address(
            EventNotificationMail::class,
            EventNotificationMail::createRecipients($followers),
            EventNotificationMail::createRecipientFromAdherent($host),
            EventNotificationMail::createTemplateVars(
                $committeeEvent,
                $host,
                $this->generateUrl('app_event_show', ['slug' => $committeeEvent->getSlug()]),
                $this->generateUrl('app_event_attend', ['slug' => $committeeEvent->getSlug()])
            ),
            EventNotificationMail::createSubject($committeeEvent)
        );
    }

    public function onEventCancelled(EventEvent $event): void
    {
        if (!$event->getCommittee()) {
            return;
        }

        if (!$event->getEvent()->isCancelled()) {
            return;
        }

        $subscriptions = $this->registrationRepository->findByEvent($event->getEvent());
        $committeeEvent = $event->getEvent();

        $this->mailPost->address(
            EventCancellationMail::class,
            EventCancellationMail::createRecipients($subscriptions),
            EventCancellationMail::createReplyTo($event->getAuthor()),
            EventCancellationMail::createTemplateVars($committeeEvent, $this->generateUrl('app_search_events')),
            EventCancellationMail::createSubject($committeeEvent)
        );
    }

    private function generateUrl(string $route, array $params = []): string
    {
        return $this->urlGenerator->generate($route, $params, UrlGeneratorInterface::ABSOLUTE_URL);
    }

    public static function getSubscribedEvents(): array
    {
        return [
            Events::EVENT_CREATED => ['onEventCreated', -128],
            Events::EVENT_CANCELLED => ['onEventCancelled', -128],
        ];
    }
}
