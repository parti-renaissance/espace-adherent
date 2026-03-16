<?php

declare(strict_types=1);

namespace App\JMEFilter\FilterBuilder;

use App\JMEFilter\FilterCollectionBuilder;
use App\Scope\FeatureEnum;

class SmsSubscriptionStatusFilterBuilder implements FilterBuilderInterface
{
    public function supports(string $scope, ?string $feature = null): bool
    {
        return FeatureEnum::CONTACTS === $feature;
    }

    public function build(string $scope, ?string $feature = null, bool $isVox = false): array
    {
        $filters = new FilterCollectionBuilder()
            ->createBooleanSelect('smsSubscription', 'Abonné aux SMS')
            ->getFilters()
        ;

        if ($isVox) {
            foreach ($filters as $filter) {
                $filter->setPosition(2);
            }
        }

        return $filters;
    }
}
