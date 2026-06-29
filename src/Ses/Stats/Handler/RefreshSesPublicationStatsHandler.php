<?php

declare(strict_types=1);

namespace App\Ses\Stats\Handler;

use App\AdherentMessage\Stats\PublicationStatsRefresher;
use App\AdherentMessage\Stats\ReportSyncDelayCalculator;
use App\Entity\AdherentMessage\AdherentMessage;
use App\Repository\AdherentMessageRepository;
use App\Repository\AppHitRepository;
use App\Ses\Stats\Command\RefreshSesPublicationStatsCommand;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\DelayStamp;

#[AsMessageHandler]
class RefreshSesPublicationStatsHandler
{
    public function __construct(
        private readonly AdherentMessageRepository $adherentMessageRepository,
        private readonly AppHitRepository $appHitRepository,
        private readonly PublicationStatsRefresher $refresher,
        private readonly ReportSyncDelayCalculator $delayCalculator,
        private readonly MessageBusInterface $bus,
    ) {
    }

    public function __invoke(RefreshSesPublicationStatsCommand $command): void
    {
        $message = $this->adherentMessageRepository->findOneByUuid($command->getUuid());
        if (!$message instanceof AdherentMessage || !$message->isSent()) {
            return;
        }

        $this->appHitRepository->markSuspiciousEmailClicks($message->getUuid()->toRfc4122());

        $this->refresher->refresh($message);

        if ($command->autoReschedule && null !== ($delay = $this->delayCalculator->calculate($message->getSentAt()))) {
            $this->bus->dispatch(new RefreshSesPublicationStatsCommand($command->getUuid()), [new DelayStamp($delay)]);
        }
    }
}
