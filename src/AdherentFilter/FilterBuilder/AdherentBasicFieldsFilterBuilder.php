<?php

namespace App\AdherentFilter\FilterBuilder;

use App\Filter\FilterCollectionBuilder;
use App\Filter\Types\DefinedTypes\AgeRange;
use App\Filter\Types\DefinedTypes\GenderSelect;
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
            ->createFrom(GenderSelect::class)
            ->createText('firstName', 'Prénom')
            ->createText('lastName', 'Nom')
            ->createFrom(AgeRange::class)
            ->createDateInterval('registeredAt', 'Date d\'adhésion')
            ->createFrom(ZoneAutocomplete::class)
            ->createBooleanSelect('isCommitteeMember', 'Membre d\'un comité')
            ->getFilters()
        ;
    }
}
