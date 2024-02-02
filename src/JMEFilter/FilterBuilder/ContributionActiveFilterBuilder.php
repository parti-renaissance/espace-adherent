<?php

namespace App\JMEFilter\FilterBuilder;

use App\JMEFilter\FilterCollectionBuilder;
use App\JMEFilter\FilterGroup\ElectedRepresentativeFilterGroup;
use App\Scope\FeatureEnum;

class ContributionActiveFilterBuilder implements FilterBuilderInterface
{
    public function supports(string $scope, ?string $feature = null): bool
    {
        return FeatureEnum::ELECTED_REPRESENTATIVE === $feature;
    }

    public function build(string $scope, ?string $feature = null): array
    {
        return (new FilterCollectionBuilder())
            ->createBooleanSelect('contributionActive', 'Cotisation active')
            ->setPosition(11)
            ->getFilters()
        ;
    }

    public function getGroup(): string
    {
        return ElectedRepresentativeFilterGroup::class;
    }
}
