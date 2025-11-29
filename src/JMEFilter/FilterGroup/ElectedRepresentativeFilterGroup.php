<?php

declare(strict_types=1);

namespace App\JMEFilter\FilterGroup;

use App\Scope\FeatureEnum;

class ElectedRepresentativeFilterGroup extends AbstractFilterGroup
{
    protected const LABEL = 'Élu';
    protected const COLOR = '#2563EB';

    protected function initialize(string $scope, ?string $feature = null): void
    {
        if (FeatureEnum::PUBLICATIONS === $feature) {
            $this->label = 'Filtres élus';
        }
    }
}
