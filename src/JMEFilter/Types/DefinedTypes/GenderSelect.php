<?php

namespace App\JMEFilter\Types\DefinedTypes;

use App\JMEFilter\Types\Select;
use App\ValueObject\Genders;

class GenderSelect extends Select
{
    public function __construct(array $options = [])
    {
        parent::__construct($options['code'] ?? 'gender', $options['label'] ?? 'Civilité');

        $this->setChoices(Genders::CHOICES_LABELS);
    }
}
