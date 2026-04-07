<?php

declare(strict_types=1);

namespace App\Event\Handler;

use App\Event\Command\EventLiveBeginEmailChunkNotificationCommand;
use App\Event\Command\EventLiveBeginEmailNotificationCommand;
use App\Mailer\MailerService;
use App\Repository\AdherentRepository;
use App\Repository\Event\EventRepository;
use App\Subscription\SubscriptionTypeEnum;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsMessageHandler]
class EventLiveBeginNotificationCommandHandler
{
    public function __construct(
        private readonly EventRepository $eventRepository,
        private readonly AdherentRepository $adherentRepository,
        private readonly MessageBusInterface $bus,
    ) {
    }

    public function __invoke(EventLiveBeginEmailNotificationCommand $command): void
    {
        if (!$event = $this->eventRepository->findOneByUuid($command->getUuid())) {
            return;
        }

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
