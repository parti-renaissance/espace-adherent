<?php

namespace App\AdherentFilter\FilterBuilder;

use App\Filter\FilterCollectionBuilder;
use App\Filter\Types\DefinedTypes\ZoneAutocomplete;
use App\Scope\FeatureEnum;
use App\Scope\ScopeEnum;

class ZoneAutocompleteFilterBuilder implements AdherentFilterBuilderInterface
{
    public function supports(string $scope, string $feature = null): bool
    {
        return \in_array($scope, ScopeEnum::ALL, true);
    }

    public function build(string $scope, string $feature = null): array
    {
        return (new FilterCollectionBuilder())
            ->createFrom(ZoneAutocomplete::class, ['code' => FeatureEnum::MESSAGES === $feature ? 'zone' : 'zones'])
            ->setMultiple(FeatureEnum::MESSAGES !== $feature)
            ->getFilters()
        ;
    }
}
