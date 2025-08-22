<?php

namespace App\JMEFilter\FilterBuilder;

use App\JMEFilter\FilterCollectionBuilder;
use App\JMEFilter\FilterGroup\MilitantFilterGroup;

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

    public function getGroup(): string
    {
        return MilitantFilterGroup::class;
    }
}
