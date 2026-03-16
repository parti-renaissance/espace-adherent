<?php

declare(strict_types=1);

namespace App\JMEFilter\FilterBuilder;

use App\JMEFilter\FilterCollectionBuilder;
use App\Scope\FeatureEnum;

class MilitantFilterBuilder implements FilterBuilderInterface
{
    public function supports(string $scope, ?string $feature = null): bool
    {
        return true;
    }

    public function build(string $scope, ?string $feature = null, bool $isVox = false): array
    {
        return new FilterCollectionBuilder()
            ->createDateInterval('registered', 'Création du compte')
            ->setPosition(\in_array($feature, [FeatureEnum::MESSAGES, FeatureEnum::PUBLICATIONS]) ? 99 : 200)
            ->getFilters()
        ;
    }
}
