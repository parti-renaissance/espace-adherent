<?php

namespace App\JMEFilter\FilterBuilder;

use App\JMEFilter\FilterCollectionBuilder;
use App\JMEFilter\FilterGroup\PersonalInformationsFilterGroup;
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

    public function getGroup(): string
    {
        return PersonalInformationsFilterGroup::class;
    }
}
