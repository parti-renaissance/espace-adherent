<?php

namespace App\JeMengage\Hit\Stats;

use App\JeMengage\Hit\Stats\DTO\StatsOutput;
use App\JeMengage\Hit\TargetTypeEnum;
use App\Repository\AppHitRepository;
use Ramsey\Uuid\UuidInterface;

class Aggregator
{
    public function __construct(private readonly AppHitRepository $hitRepository)
    {
    }

    public function getStats(TargetTypeEnum $type, UuidInterface $objectUuid): StatsOutput
    {
        return new StatsOutput(...array_values($this->hitRepository->countImpressionAndOpenStats($type, $objectUuid)));
    }
}
