<?php

declare(strict_types=1);

namespace App\JMEFilter\FilterBuilder;

use App\JMEFilter\FilterCollectionBuilder;
use App\JMEFilter\FilterGroup\MilitantFilterGroup;
use App\JMEFilter\FilterGroup\PersonalInformationsFilterGroup;
use App\JMEFilter\Types\DefinedTypes\AgeRange;
use App\JMEFilter\Types\DefinedTypes\GenderSelect;
use App\Scope\FeatureEnum;

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
            ->withEmptyChoice(FeatureEnum::PUBLICATIONS === $feature)
            ->createFrom(AgeRange::class)
            ->getFilters()
        ;
    }

    public function getGroup(string $scope, ?string $feature = null): string
    {
        if (FeatureEnum::PUBLICATIONS === $feature) {
            return MilitantFilterGroup::class;
        }

        return PersonalInformationsFilterGroup::class;
    }
}
