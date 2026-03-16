<?php

declare(strict_types=1);

namespace App\JMEFilter\FilterBuilder;

use App\JMEFilter\FilterCollectionBuilder;
use App\Scope\ScopeEnum;

class CommitteeMemberFilterBuilder implements FilterBuilderInterface
{
    public function supports(string $scope, ?string $feature = null): bool
    {
        return \in_array($scope, [ScopeEnum::PRESIDENT_DEPARTMENTAL_ASSEMBLY, ScopeEnum::NATIONAL], true);
    }

    public function build(string $scope, ?string $feature = null, bool $isVox = false): array
    {
        return new FilterCollectionBuilder()
            ->createBooleanSelect('is_committee_member', 'Membre d\'un comité')
            ->getFilters()
        ;
    }
}
