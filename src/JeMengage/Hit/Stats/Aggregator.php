<?php

namespace App\JeMengage\Hit\Stats;

use App\JeMengage\Hit\Stats\DTO\StatsOutput;
use App\JeMengage\Hit\Stats\Provider\ProviderInterface;
use App\JeMengage\Hit\TargetTypeEnum;
use Ramsey\Uuid\UuidInterface;

class Aggregator
{
    public function __construct(private readonly iterable $providers)
    {
    }

    public function getStats(TargetTypeEnum $type, UuidInterface $objectUuid): StatsOutput
    {
        $output = new StatsOutput();

        /** @var ProviderInterface $provider */
        foreach ($this->providers as $provider) {
            if ($provider->support($type)) {
                $output->push($provider->provide($type, $objectUuid, $output));
            }
        }

        return $output;
    }
}
