<?php

namespace App\Filter\FilterBuilder;

use App\Filter\FilterCollectionBuilder;
use App\Scope\FeatureEnum;

class SmsSubscriptionStatusFilterBuilder implements FilterBuilderInterface
{
    public function supports(string $scope, string $feature = null): bool
    {
        return FeatureEnum::CONTACTS === $feature;
    }

    public function build(string $scope, string $feature = null): array
    {
        return (new FilterCollectionBuilder())
            ->createBooleanSelect('smsSubscription', 'Abonné SMS')
            ->getFilters()
        ;
    }
}
