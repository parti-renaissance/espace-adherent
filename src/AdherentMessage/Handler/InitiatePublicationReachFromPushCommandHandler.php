<?php

declare(strict_types=1);

namespace App\AdherentMessage\Handler;

use App\AdherentMessage\Command\CreatePublicationReachFromPushCommand;
use App\AdherentMessage\Command\InitiatePublicationReachFromPushCommand;
use App\Entity\Notification;
use App\Repository\NotificationRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsMessageHandler]
class InitiatePublicationReachFromPushCommandHandler
{
    public function __construct(
        private readonly NotificationRepository $notificationRepository,
        private readonly MessageBusInterface $bus,
    ) {
    }

    public function __invoke(InitiatePublicationReachFromPushCommand $command): void
    {
        /** @var Notification|null $notification */
        $notification = $this->notificationRepository->findOneByUuid($command->getUuid()->toString());
        if (!$notification) {
            return;
        }

        if (!$adherentMessageId = (int) (explode(':', $notification->getScope(), 2)[1] ?? null)) {
            return;
        }

        foreach ($notification->getTokens() as $token) {
            $this->bus->dispatch(new CreatePublicationReachFromPushCommand($adherentMessageId, $token));
        }
    }
}
