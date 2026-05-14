<?php

declare(strict_types=1);

namespace App\JeMengage\Hit\Stats;

use App\JeMengage\Hit\Stats\DTO\StatsOutput;
use App\JeMengage\Hit\TargetTypeEnum;
use Symfony\Component\Uid\Uuid;

interface AggregatorInterface
{
    public function getStats(TargetTypeEnum $type, Uuid $objectUuid, bool $wait = false): StatsOutput;
}
