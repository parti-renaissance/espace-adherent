<?php

namespace App\EventListener;

use App\CitizenAction\CitizenActionEvent;
use App\Entity\BaseEvent;
use App\Entity\CitizenAction;
use App\Entity\PostAddress;
use App\Event\EventEvent;
use App\Events;
use App\Mailer\MailerService;
use App\Mailer\Message\CitizenActionUpdateMessage;
use App\Mailer\Message\EventUpdateMessage;
use App\Repository\EventRegistrationRepository;
use DateTimeInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class SendEventUpdateNotificationListener implements EventSubscriberInterface
{
    /** @var DateTimeInterface */
    private $eventBeginAt;
    /** @var DateTimeInterface */
    private $eventFinishAt;
    /** @var PostAddress */
    private $postAddress;

    private $registrationRepository;
    private $mailer;
    private $urlGenerator;

    public function __construct(
        EventRegistrationRepository $registrationRepository,
        MailerService $mailer,
        UrlGeneratorInterface $urlGenerator
    ) {
        $this->registrationRepository = $registrationRepository;
        $this->mailer = $mailer;
        $this->urlGenerator = $urlGenerator;
    }

    public static function getSubscribedEvents()
    {
        return [
            Events::EVENT_PRE_UPDATE => 'onEventPreUpdate',
            Events::EVENT_UPDATED => 'onEventPostUpdate',

            Events::CITIZEN_ACTION_PRE_UPDATE => 'onCitizenActionPreUpdate',
            Events::CITIZEN_ACTION_UPDATED => 'onCitizenActionPostUpdate',
        ];
    }

    public function onEventPreUpdate(EventEvent $event): void
    {
        $this->doPreUpdate($event->getEvent());
    }

    public function onCitizenActionPreUpdate(CitizenActionEvent $event): void
    {
        $this->doPreUpdate($event->getCitizenAction());
    }

    public function onEventPostUpdate(EventEvent $event): void
    {
        $this->doPostUpdate($event->getEvent());
    }

    public function onCitizenActionPostUpdate(CitizenActionEvent $event): void
    {
        $this->doPostUpdate($event->getCitizenAction());
    }

    private function matchChanges(BaseEvent $event): bool
    {
        if (!$this->postAddress || !$this->eventBeginAt || !$this->eventFinishAt) {
            return false;
        }

        return !$this->postAddress->equals($event->getPostAddressModel())
            || $this->eventBeginAt != $event->getBeginAt()
            || $this->eventFinishAt != $event->getFinishAt()
        ;
    }

    private function doPreUpdate(BaseEvent $event): void
    {
        $this->postAddress = clone $event->getPostAddressModel();
        $this->eventBeginAt = clone $event->getBeginAt();
        $this->eventFinishAt = clone $event->getFinishAt();
    }

    private function doPostUpdate(BaseEvent $event): void
    {
        if ($this->matchChanges($event)) {
            $subscriptions = $this->registrationRepository->findByEvent($event);

            if (\count($subscriptions) > 0) {
                $chunks = array_chunk($subscriptions->toArray(), MailerService::PAYLOAD_MAXSIZE);

                if ($event instanceof CitizenAction) {
                    $messageClass = CitizenActionUpdateMessage::class;
                    $eventRoute = 'app_citizen_action_show';
                    $icalEventRoute = 'app_citizen_action_export_ical';
                } else {
                    $messageClass = EventUpdateMessage::class;
                    $eventRoute = 'app_event_show';
                    $icalEventRoute = 'app_event_export_ical';
                }

                foreach ($chunks as $recipient) {
                    $this->mailer->sendMessage(
                        $messageClass::create(
                            $recipient,
                            $event->getOrganizer(),
                            $event,
                            $this->urlGenerator->generate($eventRoute, ['slug' => $event->getSlug()], UrlGeneratorInterface::ABSOLUTE_URL),
                            $this->urlGenerator->generate($icalEventRoute, ['slug' => $event->getSlug()], UrlGeneratorInterface::ABSOLUTE_URL)
                        )
                    );
                }
            }
        }
    }
}
