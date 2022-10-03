<?php

namespace App\AdherentFilter\FilterBuilder;

use App\Filter\FilterCollectionBuilder;
use App\Scope\FeatureEnum;
use App\Scope\ScopeEnum;

class ActiveMembershipFilterBuilder implements AdherentFilterBuilderInterface
{
    public function supports(string $scope, string $feature = null): bool
    {
        return \in_array($scope, [ScopeEnum::REFERENT], true)
            && FeatureEnum::MESSAGES === $feature
        ;
    }

    public function build(string $scope, string $feature = null): array
    {
        return (new FilterCollectionBuilder())
            ->createBooleanSelect('isActiveMembership', 'Adhérent Renaissance')
            ->getFilters()
        ;
    }
}
