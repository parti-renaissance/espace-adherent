<?php

declare(strict_types=1);

namespace App\Event\Handler;

use App\Entity\Event\Event;
use App\Event\Command\EventLiveBeginEmailChunkNotificationCommand;
use App\Event\Command\EventLiveBeginNotificationCommand;
use App\Event\Command\EventLiveBeginPushChunkNotificationCommand;
use App\Firebase\JeMarcheMessaging;
use App\Mailer\MailerService;
use App\Repository\AdherentRepository;
use App\Repository\Event\EventRepository;
use App\Repository\PushTokenRepository;
use App\Subscription\SubscriptionTypeEnum;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsMessageHandler]
class EventLiveBeginNotificationCommandHandler
{
    public function __construct(
        private readonly EventRepository $eventRepository,
        private readonly AdherentRepository $adherentRepository,
        private readonly PushTokenRepository $pushTokenRepository,
        private readonly MessageBusInterface $bus,
    ) {
    }

    public function __invoke(EventLiveBeginNotificationCommand $command): void
    {
        if (!$event = $this->eventRepository->findOneByUuid($command->getUuid())) {
            return;
        }

        $this->dispatchPushNotifications($event);
        $this->dispatchEmailNotifications($event);
    }

    private function dispatchPushNotifications(Event $event): void
    {
        $pushTokenIds = $this->pushTokenRepository->findAllIdsForNational();

        if (empty($pushTokenIds)) {
            return;
        }

        foreach (array_chunk($pushTokenIds, JeMarcheMessaging::MULTICAST_MAX_TOKENS) as $index => $chunk) {
            $this->bus->dispatch(new EventLiveBeginPushChunkNotificationCommand(
                $event->getUuid(),
                $chunk,
                \sprintf('event_live:%s:batch_push:%s', $event->getId(), $index)
            ));
        }
    }

    private function dispatchEmailNotifications(Event $event): void
    {
        $recipientIds = $this->adherentRepository->findAdherentIdsWithSubscriptionTypes([SubscriptionTypeEnum::EVENT_EMAIL]);

        if (empty($recipientIds)) {
            return;
        }

        foreach (array_chunk($recipientIds, MailerService::PAYLOAD_MAXSIZE) as $index => $chunk) {
            $this->bus->dispatch(new EventLiveBeginEmailChunkNotificationCommand(
                $event->getUuid(),
                $chunk,
                \sprintf('event_live:%s:batch_email:%s', $event->getId(), $index)
            ));
        }
    }
}
