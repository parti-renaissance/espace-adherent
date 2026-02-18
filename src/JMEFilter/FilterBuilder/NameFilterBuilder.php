<?php

declare(strict_types=1);

namespace App\JMEFilter\FilterBuilder;

use App\JMEFilter\FilterCollectionBuilder;
use App\JMEFilter\FilterGroup\PersonalInformationsFilterGroup;
use App\Scope\FeatureEnum;

class NameFilterBuilder implements FilterBuilderInterface
{
    public function supports(string $scope, ?string $feature = null): bool
    {
        return \in_array($feature, [FeatureEnum::MESSAGES, FeatureEnum::CONTACTS], true);
    }

    public function build(string $scope, ?string $feature = null): array
    {
        return new FilterCollectionBuilder()
            ->createText('first_name', 'Prénom')
            ->createText('last_name', 'Nom')
            ->getFilters()
        ;
    }

    public function getGroup(string $scope, ?string $feature = null): string
    {
        return PersonalInformationsFilterGroup::class;
    }
}
