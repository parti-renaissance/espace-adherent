<?php

namespace App\Procuration\V2\Handler;

use App\Entity\ProcurationV2\ProcurationRequest;
use App\Procuration\V2\Command\InitialRequestReminderCommand;
use App\Procuration\V2\ProcurationNotifier;
use App\Repository\Procuration\ProcurationRequestRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class InitialRequestReminderCommandHandler
{
    public function __construct(
        private readonly ProcurationRequestRepository $procurationRequestRepository,
        private readonly ProcurationNotifier $procurationNotifier
    ) {
    }

    public function __invoke(InitialRequestReminderCommand $command): void
    {
        $initialRequest = $this->procurationRequestRepository->findOneByUuid($command->getUuid());

        if (
            !$initialRequest instanceof ProcurationRequest
            || $initialRequest->isReminded()
        ) {
            return;
        }

        $this->procurationNotifier->sendInitialRequestReminder($initialRequest);

        $initialRequest->remind();

        $this->procurationRequestRepository->save($initialRequest);
    }
}
