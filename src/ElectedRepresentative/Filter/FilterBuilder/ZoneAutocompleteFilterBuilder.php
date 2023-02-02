<?php

namespace App\ElectedRepresentative\Filter\FilterBuilder;

use App\Filter\FilterCollectionBuilder;
use App\Filter\Types\DefinedTypes\ZoneAutocomplete;
use App\Scope\ScopeEnum;

class ZoneAutocompleteFilterBuilder implements ElectedRepresentativeFilterBuilderInterface
{
    public function supports(string $scope): bool
    {
        return \in_array($scope, ScopeEnum::ALL, true);
    }

    public function build(string $scope): array
    {
        return (new FilterCollectionBuilder())
            ->createFrom(ZoneAutocomplete::class, [
                'code' => 'zones',
                'zone_types' => [],
            ])
            ->setMultiple(true)
            ->setRequired(false)
            ->getFilters()
        ;
    }
}
