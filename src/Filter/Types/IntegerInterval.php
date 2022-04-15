<?php

namespace App\Filter\Types;

use App\Filter\FilterTypeEnum;

class IntegerInterval extends AbstractFilter
{
    public function addFirstOption(string $option, $value): void
    {
        $options = $this->getOptions()['first'] ?? [];

        $options[$option] = $value;

        $this->addOption('first', $options);
    }

    public function addSecondOption(string $option, $value): void
    {
        $options = $this->getOptions()['second'] ?? [];

        $options[$option] = $value;

        $this->addOption('second', $options);
    }

    protected function _getType(): string
    {
        return FilterTypeEnum::INTEGER_INTERVAL;
    }
}
