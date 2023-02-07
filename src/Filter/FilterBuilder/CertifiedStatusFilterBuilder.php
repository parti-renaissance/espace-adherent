<?php

namespace App\Filter\FilterBuilder;

use App\Filter\FilterCollectionBuilder;
use App\Scope\FeatureEnum;
use App\Scope\ScopeEnum;

class CertifiedStatusFilterBuilder implements FilterBuilderInterface
{
    public function supports(string $scope, string $feature = null): bool
    {
        return \in_array($scope, [ScopeEnum::REFERENT, ScopeEnum::NATIONAL], true)
            && FeatureEnum::ELECTED_REPRESENTATIVE !== $feature
        ;
    }

    public function build(string $scope, string $feature = null): array
    {
        return (new FilterCollectionBuilder())
            ->createBooleanSelect('isCertified', 'Certifié')
            ->getFilters()
        ;
    }
}
