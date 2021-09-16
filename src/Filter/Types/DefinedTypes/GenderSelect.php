<?php

namespace App\Filter\Types\DefinedTypes;

use App\Filter\Types\Select;
use App\ValueObject\Genders;

class GenderSelect extends Select
{
    public function __construct()
    {
        parent::__construct('gender', 'Genre');

        $this->setChoices(Genders::CHOICES_LABELS);
    }
}
