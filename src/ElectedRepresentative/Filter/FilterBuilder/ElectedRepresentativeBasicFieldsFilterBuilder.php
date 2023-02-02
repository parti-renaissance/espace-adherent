<?php

namespace App\ElectedRepresentative\Filter\FilterBuilder;

use App\Filter\FilterCollectionBuilder;
use App\Filter\Types\DefinedTypes\GenderSelect;
use App\Scope\ScopeEnum;

class ElectedRepresentativeBasicFieldsFilterBuilder implements ElectedRepresentativeFilterBuilderInterface
{
    public function supports(string $scope): bool
    {
        return \in_array($scope, ScopeEnum::ALL, true);
    }

    public function build(string $scope): array
    {
        return (new FilterCollectionBuilder())
            ->createFrom(GenderSelect::class)
            ->createText('firstName', 'Prénom')
            ->createText('lastName', 'Nom')
            ->createBooleanSelect('emailSubscribed', 'Abonné email')
            ->getFilters()
        ;
    }
}
