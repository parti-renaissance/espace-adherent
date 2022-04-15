<?php

namespace App\Filter\Types\DefinedTypes;

use App\Filter\Types\IntegerInterval;

class AgeRange extends IntegerInterval
{
    public function __construct()
    {
        parent::__construct('age', 'Âge');

        $this->addFirstOption('min', 1);
        $this->addSecondOption('min', 1);
    }
}
