<?php

namespace App\EventListener;

use App\Coalition\CoalitionUrlGenerator;
use App\Entity\Event\BaseEvent;
use App\Entity\Event\CauseEvent;
use App\Entity\Event\CoalitionEvent;
use App\Entity\PostAddress;
use App\Event\CommitteeEventEvent;
use App\Event\EventEvent;
use App\Events;
use App\Mailer\MailerService;
use App\Mailer\Message\Coalition\CoalitionsEventUpdateMessage;
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
    private $coalitionUrlGenerator;

    public function __construct(
        EventRegistrationRepository $registrationRepository,
        MailerService $transactionalMailer,
        UrlGeneratorInterface $urlGenerator,
        CoalitionUrlGenerator $coalitionUrlGenerator
    ) {
        $this->registrationRepository = $registrationRepository;
        $this->mailer = $transactionalMailer;
        $this->urlGenerator = $urlGenerator;
        $this->coalitionUrlGenerator = $coalitionUrlGenerator;
    }

    public static function getSubscribedEvents()
    {
        return [
            Events::EVENT_PRE_UPDATE => 'onEventPreUpdate',
            Events::EVENT_UPDATED => 'onEventPostUpdate',
        ];
    }

    public function onEventPreUpdate(EventEvent $event): void
    {
        if ($event instanceof CommitteeEventEvent || $event->isCoalitionsEvent()) {
            $this->doPreUpdate($event->getEvent());
        }
    }

    public function onEventPostUpdate(EventEvent $event): void
    {
        if ($event instanceof CommitteeEventEvent || $event->isCoalitionsEvent()) {
            $this->doPostUpdate($event->getEvent());
        }
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
                $isCoalitionsEvent = $event instanceof CoalitionEvent || $event instanceof CauseEvent;

                foreach ($chunks as $recipient) {
                    $this->mailer->sendMessage(
                        $isCoalitionsEvent
                            ? CoalitionsEventUpdateMessage::create(
                            $recipient,
                            $event,
                            $event instanceof CoalitionEvent
                                ? $this->coalitionUrlGenerator->generateCoalitionEventLink($event)
                                : $this->coalitionUrlGenerator->generateCauseEventLink($event),
                        )
                        : EventUpdateMessage::create(
                            $recipient,
                            $event->getOrganizer(),
                            $event,
                            $this->urlGenerator->generate('app_committee_event_show', ['slug' => $event->getSlug()], UrlGeneratorInterface::ABSOLUTE_URL),
                            $this->urlGenerator->generate('app_committee_event_export_ical', ['slug' => $event->getSlug()], UrlGeneratorInterface::ABSOLUTE_URL)
                        )
                    );
                }
            }
        }
    }
}
