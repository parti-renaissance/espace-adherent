<?php

namespace App\JMEFilter\FilterBuilder;

use App\JMEFilter\FilterCollectionBuilder;
use App\JMEFilter\FilterGroup\PersonalInformationsFilterGroup;
use App\JMEFilter\Types\DefinedTypes\AgeRange;
use App\JMEFilter\Types\DefinedTypes\GenderSelect;

class BasicFieldsFilterBuilder implements FilterBuilderInterface
{
    public function supports(string $scope, ?string $feature = null): bool
    {
        return true;
    }

    public function build(string $scope, ?string $feature = null): array
    {
        return (new FilterCollectionBuilder())
            ->createFrom(GenderSelect::class)
            ->createFrom(AgeRange::class)
            ->getFilters()
        ;
    }

    public function getGroup(): string
    {
        return PersonalInformationsFilterGroup::class;
    }
}
