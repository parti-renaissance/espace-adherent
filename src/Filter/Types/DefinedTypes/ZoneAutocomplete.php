<?php

namespace App\Filter\Types\DefinedTypes;

use App\Controller\Api\Zone\ZoneAutocompleteController;
use App\Filter\Types\Autocomplete;

class ZoneAutocomplete extends Autocomplete
{
    public function __construct(array $options = [])
    {
        parent::__construct($options['code'] ?? 'zones', $options['label'] ?? 'Zone géographique');

        $this->setUrl('/api/v3/zone/autocomplete'.(!empty($options['zone_types']) ? '?'.http_build_query([ZoneAutocompleteController::QUERY_ZONE_TYPE_PARAM => $options['zone_types']]) : ''));
        $this->setQueryParam(ZoneAutocompleteController::QUERY_SEARCH_PARAM);
        $this->setValueParam('uuid');
        $this->setLabelParam('name');
    }
}
