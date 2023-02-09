<?php

namespace App\JMEFilter\FilterBuilder;

use App\JMEFilter\FilterCollectionBuilder;
use App\Scope\FeatureEnum;

class OnlyJeMengageUsersFilterBuilder implements FilterBuilderInterface
{
    public function supports(string $scope, string $feature = null): bool
    {
        return FeatureEnum::CONTACTS === $feature;
    }

    public function build(string $scope, string $feature = null): array
    {
        return (new FilterCollectionBuilder())
            ->createBooleanSelect('onlyJeMengageUsers', 'Compte de la majorité présidentielle')
            ->getFilters()
        ;
    }
}
