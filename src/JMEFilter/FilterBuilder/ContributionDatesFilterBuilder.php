<?php

namespace App\JMEFilter\FilterBuilder;

use App\JMEFilter\FilterCollectionBuilder;
use App\JMEFilter\FilterGroup\DatesFilterGroup;
use App\JMEFilter\FilterGroup\MilitantFilterGroup;
use App\Scope\FeatureEnum;

class ContributionDatesFilterBuilder implements FilterBuilderInterface
{
    public function supports(string $scope, ?string $feature = null): bool
    {
        return true;
    }

    public function build(string $scope, ?string $feature = null): array
    {
        return (new FilterCollectionBuilder())
            ->createDateInterval('firstMembership', 'Première cotisation')
            ->setPosition(100)
            ->createDateInterval('lastMembership', 'Dernière cotisation')
            ->setPosition(100)
            ->getFilters()
        ;
    }

    public function getGroup(string $scope, ?string $feature = null): string
    {
        if (FeatureEnum::PUBLICATIONS === $feature) {
            return DatesFilterGroup::class;
        }

        return MilitantFilterGroup::class;
    }
}
