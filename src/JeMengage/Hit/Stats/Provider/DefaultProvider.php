<?php

namespace App\JeMengage\Hit\Stats\Provider;

use App\JeMengage\Hit\TargetTypeEnum;
use App\Repository\AppHitRepository;
use Ramsey\Uuid\UuidInterface;

class DefaultProvider extends AbstractProvider
{
    public function __construct(private readonly AppHitRepository $hitRepository)
    {
    }

    public function provide(TargetTypeEnum $type, UuidInterface $objectUuid): array
    {
        return $this->hitRepository->countImpressionAndOpenStats($type, $objectUuid);
    }

    public function support(TargetTypeEnum $targetType): bool
    {
        return true;
    }
}
