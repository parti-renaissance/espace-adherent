<?php

declare(strict_types=1);

namespace App\Twig;

use App\Entity\AdherentMessage\AdherentMessage;
use App\JeMengage\Hit\Stats\AggregatorInterface;
use App\JeMengage\Hit\Stats\DTO\StatsOutput;
use App\JeMengage\Hit\TargetTypeEnum;
use Twig\Extension\RuntimeExtensionInterface;

class AdminPublicationRuntime implements RuntimeExtensionInterface
{
    public function __construct(private readonly AggregatorInterface $aggregator)
    {
    }

    public function getPublicationStats(AdherentMessage $adherentMessage): StatsOutput
    {
        return $this->aggregator->getStats(TargetTypeEnum::Publication, $adherentMessage->getUuid());
    }
}
