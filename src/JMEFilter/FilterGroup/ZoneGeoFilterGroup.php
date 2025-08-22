<?php

namespace App\JMEFilter\FilterGroup;

class ZoneGeoFilterGroup extends AbstractFilterGroup
{
    protected const LABEL = 'Zone géographique';
    protected const COLOR = '#0E7490';

    public function getPosition(): int
    {
        return 0;
    }
}
