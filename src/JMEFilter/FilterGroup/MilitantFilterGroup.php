<?php

namespace App\JMEFilter\FilterGroup;

class MilitantFilterGroup extends AbstractFilterGroup
{
    protected const LABEL = 'Militant';
    protected const COLOR = '#0F766E';

    public function getPosition(): int
    {
        return 2;
    }
}
