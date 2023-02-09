<?php

namespace App\JMEFilter\FilterBuilder;

use App\JMEFilter\FilterCollectionBuilder;
use App\JMEFilter\Types\DefinedTypes\AgeRange;
use App\Scope\FeatureEnum;

class BasicFieldsForAdherentFilterBuilder implements FilterBuilderInterface
{
    public function supports(string $scope, string $feature = null): bool
    {
        return \in_array($feature, [FeatureEnum::MESSAGES, FeatureEnum::CONTACTS], true);
    }

    public function build(string $scope, string $feature = null): array
    {
        return (new FilterCollectionBuilder())
            ->createFrom(AgeRange::class)
            ->createDateInterval('registered', 'Inscrit')
            ->getFilters()
        ;
    }
}
