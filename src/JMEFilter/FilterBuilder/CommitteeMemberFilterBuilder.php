<?php

namespace App\JMEFilter\FilterBuilder;

use App\JMEFilter\FilterCollectionBuilder;
use App\JMEFilter\FilterGroup\MilitantFilterGroup;
use App\Scope\FeatureEnum;
use App\Scope\ScopeEnum;

class CommitteeMemberFilterBuilder implements FilterBuilderInterface
{
    public function supports(string $scope, ?string $feature = null): bool
    {
        return \in_array($scope, [ScopeEnum::PRESIDENT_DEPARTMENTAL_ASSEMBLY, ScopeEnum::NATIONAL], true);
    }

    public function build(string $scope, ?string $feature = null): array
    {
        return (new FilterCollectionBuilder())
            ->createBooleanSelect('isCommitteeMember', 'Membre d\'un comité')
            ->withEmptyChoice(FeatureEnum::PUBLICATIONS === $feature)
            ->getFilters()
        ;
    }

    public function getGroup(string $scope, ?string $feature = null): string
    {
        return MilitantFilterGroup::class;
    }
}
