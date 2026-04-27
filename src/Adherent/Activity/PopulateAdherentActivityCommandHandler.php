<?php

declare(strict_types=1);

namespace App\Adherent\Activity;

use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\Exception\ExceptionInterface;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsMessageHandler]
readonly class PopulateAdherentActivityCommandHandler
{
    public function __construct(
        private PopulateAdherentActivityService $service,
        private MessageBusInterface $bus,
    ) {
    }

    /**
     * @throws ExceptionInterface
     */
    public function __invoke(PopulateAdherentActivityCommand $command): void
    {
        $hasMore = $this->service->processBatch($command->sourceType);

        if ($hasMore) {
            $this->bus->dispatch(new PopulateAdherentActivityCommand($command->sourceType));
        } elseif (SourceTypeEnum::ActionHistory === $command->sourceType) {
            $this->bus->dispatch(new PopulateAdherentActivityCommand(SourceTypeEnum::Hit));
        }
    }
}
