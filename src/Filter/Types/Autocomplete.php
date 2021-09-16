<?php

namespace App\Filter\Types;

use App\Filter\FilterTypeEnum;

class Autocomplete extends AbstractFilter
{
    public function setUrl(string $url): void
    {
        $this->addOption('url', $url);
    }

    public function setQueryParam(string $value): void
    {
        $this->addOption('query_param', $value);
    }

    public function setValueParam(string $value): void
    {
        $this->addOption('value_param', $value);
    }

    public function setLabelParam(string $value): void
    {
        $this->addOption('label_param', $value);
    }

    protected function _getType(): string
    {
        return FilterTypeEnum::AUTOCOMPLETE;
    }
}
