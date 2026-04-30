<?php

declare(strict_types=1);

namespace App\Adherent\Activity;

use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\Exception\ExceptionInterface;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsMessageHandler]
class PopulateAdherentActivityCommandHandler
{
    public function __construct(
        private readonly PopulateAdherentActivityService $service,
        private readonly MessageBusInterface $bus,
        private readonly LoggerInterface $logger,
    ) {
    }

    /**
     * @throws ExceptionInterface
     */
    public function __invoke(PopulateAdherentActivityCommand $command): void
    {
        $startTime = microtime(true);
        $result = $this->service->processBatch($command->sourceType);
        $durationMs = (int) ((microtime(true) - $startTime) * 1000);

        $this->logBatchResult($command->sourceType, $result, $durationMs);

        if ($result->hasMore(PopulateAdherentActivityService::BATCH_SIZE)) {
            $this->bus->dispatch(new PopulateAdherentActivityCommand($command->sourceType));
        } elseif (SourceTypeEnum::ActionHistory === $command->sourceType) {
            $this->bus->dispatch(new PopulateAdherentActivityCommand(SourceTypeEnum::Hit));
        }
    }

    private function logBatchResult(SourceTypeEnum $sourceType, BatchResult $result, int $durationMs): void
    {
        $context = [
            'source_type' => $sourceType->value,
            'inserted' => $result->inserted,
            'last_id_before' => $result->lastIdBefore,
            'last_id_after' => $result->lastIdAfter,
            'duration_ms' => $durationMs,
        ];

        if (0 === $result->inserted) {
            $nextEligibleId = $this->service->findNextEligibleId($sourceType);
            if (null !== $nextEligibleId) {
                $this->logger->warning(
                    'AdherentActivity pipeline stalled: no row inserted while source has an eligible row',
                    $context + ['next_eligible_id' => $nextEligibleId],
                );

                return;
            }
        }

        $this->logger->info('AdherentActivity batch finished', $context);
    }
}
