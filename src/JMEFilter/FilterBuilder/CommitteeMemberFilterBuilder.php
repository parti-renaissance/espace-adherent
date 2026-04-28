<?php

declare(strict_types=1);

namespace App\JMEFilter\FilterBuilder;

use App\JMEFilter\FilterCollectionBuilder;
use App\Scope\ScopeEnum;

class CommitteeMemberFilterBuilder implements FilterBuilderInterface
{
    public function build(string $scope, ?string $feature = null, bool $isVox = false): array
    {
        if (ScopeEnum::ANIMATOR === $scope) {
            return [];
        }

        return new FilterCollectionBuilder()
            ->createBooleanSelect('is_committee_member', 'Membre d\'un comité')
            ->getFilters()
        ;
    }
}
