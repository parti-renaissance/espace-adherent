<?php

namespace App\Filter\Types\DefinedTypes;

use App\Filter\Types\IntegerInterval;

class AgeRange extends IntegerInterval
{
    private const OPTIONS = [
        'min' => 1,
        'max' => 200,
    ];

    public function __construct()
    {
        parent::__construct('age', 'Âge');

        $this->addFirstOptions(self::OPTIONS);
        $this->addSecondOptions(self::OPTIONS);
    }
}
