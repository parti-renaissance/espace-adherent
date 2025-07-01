<?php

namespace App\JMEFilter\FilterBuilder;

use App\JMEFilter\FilterCollectionBuilder;
use App\JMEFilter\FilterGroup\MilitantFilterGroup;
use App\Scope\FeatureEnum;

class ContributionDatesFilterBuilder implements FilterBuilderInterface
{
    public function supports(string $scope, ?string $feature = null): bool
    {
        return \in_array($feature, [FeatureEnum::MESSAGES, FeatureEnum::PUBLICATIONS, FeatureEnum::CONTACTS], true);
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
