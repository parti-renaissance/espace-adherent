<?php

declare(strict_types=1);

namespace App\Adhesion\Handler;

use App\Adhesion\AdherentRequestNotifier;
use App\Adhesion\Command\SendAdherentRequestReminderCommand;
use App\Repository\Renaissance\Adhesion\AdherentRequestReminderRepository;
use App\Repository\Renaissance\Adhesion\AdherentRequestRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class SendAdherentRequestReminderCommandHandler
{
    public function __construct(
        private readonly AdherentRequestRepository $adherentRequestRepository,
        private readonly AdherentRequestReminderRepository $adherentRequestReminderRepository,
        private readonly AdherentRequestNotifier $adherentRequestNotifier,
    ) {
    }

    public function __invoke(SendAdherentRequestReminderCommand $command): void
    {
        if (!$adherentRequest = $this->adherentRequestRepository->findOneByUuid($command->getUuid())) {
            return;
        }

        if (!$adherentRequest->email) {
            return;
        }

        $reminderType = $command->reminderType;

        if ($this->adherentRequestReminderRepository->hasBeenReminded($adherentRequest, $reminderType)) {
            return;
        }

        $this->adherentRequestNotifier->sendReminderMessage($adherentRequest, $reminderType);
    }
}
