<?php

declare(strict_types=1);

namespace App\JeMengage\Hit\Stats\Provider;

use App\JeMengage\Hit\Stats\DTO\StatsOutput;
use App\JeMengage\Hit\TargetTypeEnum;
use App\Repository\AppHitRepository;
use Ramsey\Uuid\UuidInterface;

class DefaultProvider extends AbstractProvider
{
    public function __construct(private readonly AppHitRepository $hitRepository)
    {
    }

    public function provide(TargetTypeEnum $type, UuidInterface $objectUuid, StatsOutput $output, bool $wait = false): array
    {
        return $this->hitRepository->countImpressionAndOpenStats($type, $objectUuid);
    }

    public function support(TargetTypeEnum $targetType): bool
    {
        return true;
    }
}
