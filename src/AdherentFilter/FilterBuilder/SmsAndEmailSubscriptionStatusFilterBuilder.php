<?php

namespace App\AdherentFilter\FilterBuilder;

use App\Filter\FilterCollectionBuilder;
use App\Scope\FeatureEnum;

class SmsAndEmailSubscriptionStatusFilterBuilder implements AdherentFilterBuilderInterface
{
    public function supports(string $scope, string $feature = null): bool
    {
        return \in_array($feature, [FeatureEnum::CONTACTS, FeatureEnum::PHONING], true);
    }

    public function build(string $scope, string $feature = null): array
    {
        return (new FilterCollectionBuilder())
            ->createBooleanSelect('emailSubscription', 'Abonné email')
            ->createBooleanSelect('smsSubscription', 'Abonné SMS')
            ->getFilters()
        ;
    }
}
