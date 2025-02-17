<?php

namespace App\Event\EventListener;

use App\Address\AddressInterface;
use App\Entity\Event\Event;
use App\Event\Command\SendCreationNotificationCommand;
use App\Event\EventEvent;
use App\Event\EventRegistrationEvent;
use App\Events;
use App\Mailer\MailerService;
use App\Mailer\Message\Renaissance\EventCancellationMessage;
use App\Mailer\Message\Renaissance\EventRegistrationConfirmationMessage;
use App\Mailer\Message\Renaissance\EventUpdateMessage;
use App\Repository\EventRegistrationRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\DelayStamp;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class EventMessageNotifierListener implements EventSubscriberInterface
{
    private ?string $visioUrl = null;
    private ?\DateTimeInterface $eventBeginAt = null;
    private ?\DateTimeInterface $eventFinishAt = null;
    private ?AddressInterface $postAddress = null;

    public function __construct(
        private readonly MailerService $transactionalMailer,
        private readonly EventRegistrationRepository $registrationRepository,
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly MessageBusInterface $bus,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            Events::EVENT_CREATED => ['onEventCreated', -128],
            Events::EVENT_CANCELLED => ['onEventCancelled', -128],
            Events::EVENT_REGISTRATION_CREATED => 'sendRegistrationEmail',
            Events::EVENT_PRE_UPDATE => 'onEventPreUpdate',
            Events::EVENT_UPDATED => 'onEventPostUpdate',
        ];
    }

    public function onEventCreated(EventEvent $event): void
    {
        $event = $event->getEvent();

        if ($event->sendInvitationEmail) {
            $this->bus->dispatch(new SendCreationNotificationCommand($event->getUuid()), [new DelayStamp(600000)]);
        }
    }

    public function onEventCancelled(EventEvent $event): void
    {
        $event = $event->getEvent();

        if (!$event->isCancelled()) {
            return;
        }

        if (!$subscriptions = $this->registrationRepository->findByEvent($event)->toArray()) {
            return;
        }

        foreach (array_chunk($subscriptions, MailerService::PAYLOAD_MAXSIZE) as $chunk) {
            $this->transactionalMailer->sendMessage(
                EventCancellationMessage::create(
                    $chunk,
                    $event,
                    $this->generateUrl('vox_app').'/evenements',
                )
            );
        }
    }

    public function sendRegistrationEmail(EventRegistrationEvent $event): void
    {
        if (!$event->getSendMail()) {
            return;
        }

        $registration = $event->getRegistration();

        $this->transactionalMailer->sendMessage(EventRegistrationConfirmationMessage::createFromRegistration(
            $registration,
            $this->generateUrl('vox_app').'/evenements/'.$registration->getEvent()->getSlug(),
        ));
    }

    public function onEventPreUpdate(EventEvent $event): void
    {
        if ($event->needSendMessage()) {
            $event = $event->getEvent();
            $this->visioUrl = $event->getVisioUrl();
            $this->postAddress = clone $event->getPostAddress();
            $this->eventBeginAt = clone $event->getBeginAt();
            $this->eventFinishAt = clone $event->getFinishAt();
        }
    }

    public function onEventPostUpdate(EventEvent $event): void
    {
        if (!$event->needSendMessage()) {
            return;
        }

        $event = $event->getEvent();

        if (!$this->matchChanges($event)) {
            return;
        }

        if (!$subscriptions = $this->registrationRepository->findByEvent($event)->toArray()) {
            return;
        }

        foreach (array_chunk($subscriptions, MailerService::PAYLOAD_MAXSIZE) as $chunk) {
            $this->transactionalMailer->sendMessage(EventUpdateMessage::create(
                $chunk,
                $event,
                $this->generateUrl('vox_app').'/evenements/'.$event->getSlug(),
            ));
        }
    }

    private function matchChanges(Event $event): bool
    {
        return
            $this->eventBeginAt != $event->getBeginAt()
            || $this->eventFinishAt != $event->getFinishAt()
            || $this->visioUrl != $event->getVisioUrl()
            || ($this->postAddress && !$this->postAddress->equals($event->getPostAddress()));
    }

    private function generateUrl(string $route, array $params = []): string
    {
        return rtrim($this->urlGenerator->generate($route, $params, UrlGeneratorInterface::ABSOLUTE_URL), '/');
    }
}
