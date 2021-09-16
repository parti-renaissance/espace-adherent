<?php

namespace App\Filter\Types\DefinedTypes;

use App\Filter\Types\Select;
use App\ValueObject\Genders;

class GenderFilter extends Select
{
    public function __construct()
    {
        parent::__construct('gender', 'Genre');

        $this->setChoices(Genders::CHOICES_LABELS);
    }
}
