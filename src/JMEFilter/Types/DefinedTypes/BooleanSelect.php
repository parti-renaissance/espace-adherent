<?php

declare(strict_types=1);

namespace App\JMEFilter\Types\DefinedTypes;

use App\JMEFilter\Types\Select;

class BooleanSelect extends Select
{
    public function __construct(string $code, string $label)
    {
        parent::__construct($code, $label);

        $this->setChoices(['false' => 'Non', 'true' => 'Oui']);
    }
}
