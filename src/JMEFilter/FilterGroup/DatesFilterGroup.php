<?php

namespace App\JMEFilter\FilterGroup;

class DatesFilterGroup extends AbstractFilterGroup
{
    protected const LABEL = 'Filtres temporels';
    protected const COLOR = '#0E7490';

    public function getPosition(): int
    {
        return 3;
    }
}
