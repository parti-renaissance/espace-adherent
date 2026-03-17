<?php

declare(strict_types=1);

namespace App\JMEFilter\FilterBuilder;

use App\JMEFilter\FilterCollectionBuilder;

class EmailSubscriptionStatusFilterBuilder implements FilterBuilderInterface
{
    public function build(string $scope, ?string $feature = null, bool $isVox = false): array
    {
        $filters = new FilterCollectionBuilder()
            ->createBooleanSelect('emailSubscription', 'Abonné aux emails')
            ->getFilters()
        ;

        if ($isVox) {
            foreach ($filters as $filter) {
                $filter->setPosition(1);
            }
        }

        return $filters;
    }
}
