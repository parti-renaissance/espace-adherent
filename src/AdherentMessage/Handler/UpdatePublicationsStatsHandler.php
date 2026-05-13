<?php

declare(strict_types=1);

namespace App\AdherentMessage\Handler;

use App\Entity\AdherentMessage\AdherentMessage;
use App\Entity\AdherentMessage\PublicationStatistics;
use App\JeMengage\Hit\Stats\AggregatorInterface;
use App\JeMengage\Hit\TargetTypeEnum;
use App\Mailchimp\Campaign\Report\Command\SyncReportCommand;
use App\Repository\AdherentMessage\PublicationStatisticsRepository;
use App\Repository\AdherentMessageRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class UpdatePublicationsStatsHandler
{
    public function __construct(
        private readonly AggregatorInterface $aggregator,
        private readonly AdherentMessageRepository $adherentMessageRepository,
        private readonly PublicationStatisticsRepository $publicationStatisticsRepository,
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

        $statsObject = $this->publicationStatisticsRepository->findOneByMessage($adherentMessage)
            ?? new PublicationStatistics($adherentMessage);

        $statsObject->refresh($this->aggregator->getStats(TargetTypeEnum::Publication, $adherentMessage->getUuid()));

        $this->entityManager->persist($statsObject);
        $this->entityManager->flush();
    }
}
