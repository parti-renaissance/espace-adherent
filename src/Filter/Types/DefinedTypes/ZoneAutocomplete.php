<?php

namespace App\Filter\Types\DefinedTypes;

use App\Filter\Types\Autocomplete;

class ZoneAutocomplete extends Autocomplete
{
    public function __construct(array $options = [])
    {
        parent::__construct($options['code'] ?? 'zones', $options['label'] ?? 'Zone géographique');

        $this->setUrl('/api/v3/zone/autocompletion');
        $this->setQueryParam('q');
        $this->setValueParam('uuid');
        $this->setLabelParam('name');
    }
}
