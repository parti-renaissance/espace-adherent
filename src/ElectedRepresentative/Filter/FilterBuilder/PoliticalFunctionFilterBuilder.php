<?php

namespace App\ElectedRepresentative\Filter\FilterBuilder;

use App\Entity\ElectedRepresentative\PoliticalFunctionNameEnum;
use App\Filter\FilterCollectionBuilder;
use App\Scope\ScopeEnum;

class PoliticalFunctionFilterBuilder implements ElectedRepresentativeFilterBuilderInterface
{
    public function supports(string $scope): bool
    {
        return \in_array($scope, ScopeEnum::ALL, true);
    }

    public function build(string $scope): array
    {
        return (new FilterCollectionBuilder())
            ->createSelect('political_functions', 'Fonctions')
            ->setChoices(array_flip(PoliticalFunctionNameEnum::CHOICES))
            ->setMultiple(true)
            ->getFilters()
        ;
    }
}
