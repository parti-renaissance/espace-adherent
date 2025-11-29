<?php

declare(strict_types=1);

namespace App\JMEFilter\FilterGroup;

class EmptyGroup extends AbstractFilterGroup
{
    protected const LABEL = '';
    protected const COLOR = '';

    public function getPosition(): int
    {
        return 0;
    }
}
