<?php

namespace App\EventListener;

use App\AppCodeEnum;
use App\Coalition\CoalitionUrlGenerator;
use App\Entity\Event\BaseEvent;
use App\Entity\Event\CoalitionEvent;
use App\Entity\Event\EventRegistration;
use App\Entity\PostAddress;
use App\Event\EventEvent;
use App\Events;
use App\Mailer\MailerService;
use App\Mailer\Message\Coalition\CoalitionsEventUpdateMessage;
use App\Mailer\Message\EventUpdateMessage;
use App\Mailer\Message\JeMengage\JeMengageEventUpdateMessage;
use App\Mailer\Message\Message;
use App\Repository\EventRegistrationRepository;
use DateTimeInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class SendEventUpdateNotificationListener implements EventSubscriberInterface
{
    /** @var string */
    private $visioUrl;
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
        if ($event->needSendMessage()) {
            $this->doPreUpdate($event->getEvent());
        }
    }

    public function onEventPostUpdate(EventEvent $event): void
    {
        if ($event->needSendMessage()) {
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
            || $this->visioUrl != $event->getVisioUrl()
        ;
    }

    private function doPreUpdate(BaseEvent $event): void
    {
        $this->visioUrl = $event->getVisioUrl();
        $this->postAddress = clone $event->getPostAddressModel();
        $this->eventBeginAt = clone $event->getBeginAt();
        $this->eventFinishAt = clone $event->getFinishAt();
    }

    private function doPostUpdate(BaseEvent $event): void
    {
        if (!$this->matchChanges($event)) {
            return;
        }

        if (!$subscriptions = $this->registrationRepository->findByEvent($event)->toArray()) {
            return;
        }

        $apps = array_unique(array_map(function (EventRegistration $eventRegistration): ?string {
            return $eventRegistration->getSource();
        }, $subscriptions));

        foreach ($apps as $appCode) {
            $recipients = array_filter($subscriptions, function (EventRegistration $eventRegistration) use ($appCode): bool {
                return $eventRegistration->getSource() === $appCode;
            });

            foreach (array_chunk($recipients, MailerService::PAYLOAD_MAXSIZE) as $chunk) {
                $message = $this->createMessage($event, $chunk, $appCode);

                $this->mailer->sendMessage($message);
            }
        }
    }

    private function createMessage(BaseEvent $event, array $recipients, ?string $appCode): Message
    {
        if ($event->isCoalitionsEvent()) {
            return CoalitionsEventUpdateMessage::create(
                $recipients,
                $event,
                $event instanceof CoalitionEvent
                    ? $this->coalitionUrlGenerator->generateCoalitionEventLink($event)
                    : $this->coalitionUrlGenerator->generateCauseEventLink($event),
            );
        }

        if (AppCodeEnum::isJeMengageMobileApp($appCode)) {
            return JeMengageEventUpdateMessage::create(
                $recipients,
                $event->getOrganizer(),
                $event,
                $this->urlGenerator->generate('app_committee_event_export_ical', ['slug' => $event->getSlug()], UrlGeneratorInterface::ABSOLUTE_URL)
            );
        }

        return EventUpdateMessage::create(
            $recipients,
            $event->getOrganizer(),
            $event,
            $this->urlGenerator->generate('app_committee_event_show', ['slug' => $event->getSlug()], UrlGeneratorInterface::ABSOLUTE_URL),
            $this->urlGenerator->generate('app_committee_event_export_ical', ['slug' => $event->getSlug()], UrlGeneratorInterface::ABSOLUTE_URL)
        );
    }
}
