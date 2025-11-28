<?php

declare(strict_types=1);

namespace App\JMEFilter\Types\DefinedTypes;

use App\Controller\Api\Zone\AbstractZoneAutocompleteController;
use App\JMEFilter\FilterTypeEnum;
use App\JMEFilter\Types\Autocomplete;

class ZoneAutocomplete extends Autocomplete
{
    public function __construct(array $options = [])
    {
        parent::__construct($options['code'] ?? 'zones', $options['label'] ?? 'Zone géographique');

        $this->setUrl('/api/v3/zone/autocomplete'.(!empty($options['zone_types']) ? '?'.http_build_query([AbstractZoneAutocompleteController::QUERY_ZONE_TYPE_PARAM => $options['zone_types']]) : ''));
        $this->setQueryParam(AbstractZoneAutocompleteController::QUERY_SEARCH_PARAM);
        $this->setValueParam('uuid');
        $this->setLabelParam('name');
    }

    protected function _getType(): string
    {
        return FilterTypeEnum::ZONE_AUTOCOMPLETE;
    }
}
