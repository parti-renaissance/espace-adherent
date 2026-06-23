<?php

declare(strict_types=1);

namespace App\AdherentMessage\Handler;

use App\AdherentMessage\Stats\PublicationStatsRefresher;
use App\Entity\AdherentMessage\AdherentMessage;
use App\Mailchimp\Campaign\Report\Command\SyncReportCommand;
use App\Repository\AdherentMessageRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class UpdatePublicationsStatsHandler
{
    public function __construct(
        private readonly AdherentMessageRepository $adherentMessageRepository,
        private readonly PublicationStatsRefresher $refresher,
    ) {
    }

    public function __invoke(SyncReportCommand $command): void
    {
        if (3 !== $command->step) {
            return;
        }

        if (!$adherentMessage = $this->adherentMessageRepository->findOneByUuid($command->getUuid())) {
            return;
        }

        /** @var AdherentMessage $adherentMessage */
        if (!$adherentMessage->isSent()) {
            return;
        }

        $this->refresher->refresh($adherentMessage);
    }
}
