<?php

declare(strict_types=1);

namespace App\JMEFilter\Types\DefinedTypes;

use App\JMEFilter\Types\IntegerInterval;

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
