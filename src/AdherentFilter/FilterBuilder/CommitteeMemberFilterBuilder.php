<?php

namespace App\AdherentFilter\FilterBuilder;

use App\Filter\FilterCollectionBuilder;
use App\Scope\FeatureEnum;
use App\Scope\ScopeEnum;

class CommitteeMemberFilterBuilder implements AdherentFilterBuilderInterface
{
    public function supports(string $scope, string $feature = null): bool
    {
        return \in_array($scope, ScopeEnum::ALL, true)
            && (ScopeEnum::CORRESPONDENT !== $scope || FeatureEnum::CONTACTS === $feature)
        ;
    }

    public function build(string $scope, string $feature = null): array
    {
        return (new FilterCollectionBuilder())
            ->createBooleanSelect('isCommitteeMember', 'Membre d\'un comité')
            ->getFilters()
        ;
    }
}
