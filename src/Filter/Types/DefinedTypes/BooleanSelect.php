<?php

namespace App\Filter\Types\DefinedTypes;

use App\Filter\Types\Select;

class BooleanSelect extends Select
{
    public function __construct(string $code, string $label)
    {
        parent::__construct($code, $label);

        $this->setChoices([null => 'Tous', 1 => 'Oui', 2 => 'Non']);
    }
}
