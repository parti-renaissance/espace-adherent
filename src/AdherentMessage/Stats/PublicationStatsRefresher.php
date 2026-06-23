<?php

declare(strict_types=1);

namespace App\AdherentMessage\Stats;

use App\Entity\AdherentMessage\AdherentMessage;
use App\Entity\AdherentMessage\PublicationStatistics;
use App\JeMengage\Hit\Stats\AggregatorInterface;
use App\JeMengage\Hit\TargetTypeEnum;
use App\Repository\AdherentMessage\PublicationStatisticsRepository;
use Doctrine\ORM\EntityManagerInterface;

class PublicationStatsRefresher
{
    public function __construct(
        private readonly AggregatorInterface $aggregator,
        private readonly PublicationStatisticsRepository $publicationStatisticsRepository,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function refresh(AdherentMessage $message): void
    {
        $statistics = $this->publicationStatisticsRepository->findOneByMessage($message)
            ?? new PublicationStatistics($message);

        $statistics->refresh($this->aggregator->getStats(TargetTypeEnum::Publication, $message->getUuid()));

        $this->entityManager->persist($statistics);
        $this->entityManager->flush();
    }
}
