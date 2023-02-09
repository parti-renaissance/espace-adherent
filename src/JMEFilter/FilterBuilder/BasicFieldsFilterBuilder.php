<?php

namespace App\JMEFilter\FilterBuilder;

use App\JMEFilter\FilterCollectionBuilder;
use App\JMEFilter\Types\DefinedTypes\GenderSelect;
use App\Scope\ScopeEnum;

class BasicFieldsFilterBuilder implements FilterBuilderInterface
{
    public function supports(string $scope, string $feature = null): bool
    {
        return \in_array($scope, ScopeEnum::ALL, true);
    }

    public function build(string $scope, string $feature = null): array
    {
        return (new FilterCollectionBuilder())
            ->createFrom(GenderSelect::class)
            ->createText('firstName', 'Prénom')
            ->createText('lastName', 'Nom')
            ->getFilters()
        ;
    }
}
