<?php

namespace App\JeMengage\Hit\Stats\Provider;

use App\JeMengage\Hit\Stats\DTO\StatsOutput;
use App\JeMengage\Hit\TargetTypeEnum;
use Ramsey\Uuid\UuidInterface;

interface ProviderInterface
{
    public function support(TargetTypeEnum $targetType): bool;

    public function provide(TargetTypeEnum $type, UuidInterface $objectUuid, StatsOutput $output, bool $wait = false): array;
}
