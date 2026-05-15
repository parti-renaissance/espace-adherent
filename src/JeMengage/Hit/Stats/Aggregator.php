<?php

declare(strict_types=1);

namespace App\JeMengage\Hit\Stats;

use App\JeMengage\Hit\Stats\DTO\StatsOutput;
use App\JeMengage\Hit\Stats\Provider\ProviderInterface;
use App\JeMengage\Hit\TargetTypeEnum;
use Symfony\Component\Uid\Uuid;

class Aggregator implements AggregatorInterface
{
    public function __construct(private readonly iterable $providers)
    {
    }

    public function getStats(TargetTypeEnum $type, Uuid $objectUuid, bool $wait = false): StatsOutput
    {
        $output = new StatsOutput();

        /** @var ProviderInterface $provider */
        foreach ($this->providers as $provider) {
            if ($provider->support($type)) {
                $output->push($provider->provide($type, $objectUuid, $output, $wait));
            }
        }

        return $output;
    }
}
