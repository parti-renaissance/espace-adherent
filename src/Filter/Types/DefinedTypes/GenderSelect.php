<?php

namespace App\Filter\Types\DefinedTypes;

use App\Filter\Types\Select;
use App\ValueObject\Genders;

class GenderSelect extends Select
{
    public function __construct(array $options = [])
    {
        parent::__construct($options['code'] ?? 'gender', $options['label'] ?? 'Genre');

        $this->setChoices(Genders::CHOICES_LABELS);
    }
}
