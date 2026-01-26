<?php

declare(strict_types=1);

namespace App\AdherentMessage\Handler;

use App\Entity\AdherentMessage\AdherentMessage;
use App\JeMengage\Hit\Stats\AggregatorInterface;
use App\JeMengage\Hit\TargetTypeEnum;
use App\Mailchimp\Campaign\Report\Command\SyncReportCommand;
use App\Repository\AdherentMessageRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class UpdatePublicationsStatsHandler
{
    public function __construct(
        private readonly AggregatorInterface $aggregator,
        private readonly AdherentMessageRepository $adherentMessageRepository,
        private readonly EntityManagerInterface $entityManager,
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

        $statsObject = $adherentMessage->getStatistics();
        $statsObject->refresh($this->aggregator->getStats(TargetTypeEnum::Publication, $adherentMessage->getUuid()));

        $this->entityManager->flush();
    }
}
