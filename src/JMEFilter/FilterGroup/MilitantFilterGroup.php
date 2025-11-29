<?php

declare(strict_types=1);

namespace App\JMEFilter\FilterGroup;

use App\Scope\FeatureEnum;

class MilitantFilterGroup extends AbstractFilterGroup
{
    protected const LABEL = 'Militant';
    protected const COLOR = '#0F766E';

    protected function initialize(string $scope, ?string $feature = null): void
    {
        if (FeatureEnum::PUBLICATIONS === $feature) {
            $this->label = 'Filtres militants';
        }
    }

    public function getPosition(): int
    {
        return 2;
    }
}
