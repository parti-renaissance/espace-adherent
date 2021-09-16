<?php

namespace App\Filter\Types\DefinedTypes;

use App\Filter\Types\IntegerInterval;

class AgeRangeFilter extends IntegerInterval
{
    public function __construct()
    {
        parent::__construct('age', 'Âge');
    }
}
