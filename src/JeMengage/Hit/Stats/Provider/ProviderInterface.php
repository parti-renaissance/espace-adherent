<?php

declare(strict_types=1);

namespace App\JeMengage\Hit\Stats\Provider;

use App\JeMengage\Hit\Stats\DTO\StatsOutput;
use App\JeMengage\Hit\TargetTypeEnum;
use Symfony\Component\Uid\Uuid;

interface ProviderInterface
{
    public function support(TargetTypeEnum $targetType): bool;

    public function provide(TargetTypeEnum $type, Uuid $objectUuid, StatsOutput $output, bool $wait = false): array;
}
