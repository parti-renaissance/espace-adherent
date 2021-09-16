<?php

namespace App\AdherentFilter\FilterBuilder;

use App\Filter\FilterCollectionBuilder;
use App\Filter\Types\DefinedTypes\AgeRangeFilter;
use App\Filter\Types\DefinedTypes\GenderFilter;
use App\Filter\Types\DefinedTypes\ZoneAutocomplete;
use App\Scope\ScopeEnum;

class AdherentBasicFieldsFilterBuilder implements AdherentFilterBuilderInterface
{
    public function supports(string $scope, string $feature = null): bool
    {
        return \in_array($scope, ScopeEnum::ALL, true);
    }

    public function build(string $scope, string $feature = null): array
    {
        return (new FilterCollectionBuilder())
            ->createFrom(GenderFilter::class)
            ->createText('firstName', 'Prénom')
            ->createText('lastName', 'Nom')
            ->createFrom(AgeRangeFilter::class)
            ->createDateInterval('registeredAt', 'Date d\'adhésion')
            ->createFrom(ZoneAutocomplete::class)
            ->createBooleanSelect('isCommitteeMember', 'Membre d\'un comité')
            ->getFilters()
        ;
    }
}
