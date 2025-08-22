<?php

namespace App\JMEFilter\FilterBuilder;

use App\JMEFilter\FilterCollectionBuilder;
use App\JMEFilter\FilterGroup\PersonalInformationsFilterGroup;
use App\Scope\ScopeEnum;

class CertifiedStatusFilterBuilder implements FilterBuilderInterface
{
    public function supports(string $scope, ?string $feature = null): bool
    {
        return ScopeEnum::NATIONAL === $scope;
    }

    public function build(string $scope, ?string $feature = null): array
    {
        return (new FilterCollectionBuilder())
            ->createBooleanSelect('isCertified', 'Certifié')
            ->getFilters()
        ;
    }

    public function getGroup(): string
    {
        return PersonalInformationsFilterGroup::class;
    }
}
