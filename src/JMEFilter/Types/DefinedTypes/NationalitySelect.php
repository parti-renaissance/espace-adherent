<?php

declare(strict_types=1);

namespace App\JMEFilter\Types\DefinedTypes;

use App\Intl\NationalityProvider;
use App\JMEFilter\Types\Select;

class NationalitySelect extends Select
{
    public function __construct(array $options = [])
    {
        parent::__construct($options['code'] ?? 'nationality', $options['label'] ?? 'Nationalité');

        $this->setChoices(NationalityProvider::getNames());
    }
}
