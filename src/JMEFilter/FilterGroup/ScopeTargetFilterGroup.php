<?php

declare(strict_types=1);

namespace App\JMEFilter\FilterGroup;

class ScopeTargetFilterGroup extends AbstractFilterGroup
{
    protected const LABEL = 'Cadres & Équipes';
    protected const COLOR = '#F8F0FF';

    public function getPosition(): int
    {
        return 5;
    }
}
