<?php

namespace App\JMEFilter\FilterBuilder;

use App\JMEFilter\FilterCollectionBuilder;
use App\JMEFilter\FilterGroup\MilitantFilterGroup;
use App\Scope\FeatureEnum;

class MilitantFilterBuilder implements FilterBuilderInterface
{
    public function supports(string $scope, ?string $feature = null): bool
    {
        return \in_array($feature, [FeatureEnum::MESSAGES, FeatureEnum::CONTACTS], true);
    }

    public function build(string $scope, ?string $feature = null): array
    {
        $filterBuilder = (new FilterCollectionBuilder())
            ->createDateInterval('registered', 'Inscrit')
        ;

        if (FeatureEnum::CONTACTS === $feature) {
            $filterBuilder
                ->createBooleanSelect('isNewRenaissanceUser', 'Nouveau militant')
            ;
        }

        return $filterBuilder->getFilters();
    }

    public function getGroup(): string
    {
        return MilitantFilterGroup::class;
    }
}
