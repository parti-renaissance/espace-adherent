<?php

declare(strict_types=1);

namespace App\JMEFilter\Types;

use App\JMEFilter\FilterTypeEnum;

class IntegerInterval extends AbstractFilter
{
    public function addFirstOption(string $option, $value): void
    {
        $options = $this->getOptions()['first'] ?? [];

        $options[$option] = $value;

        $this->addOption('first', $options);
    }

    public function addFirstOptions(array $options): void
    {
        foreach ($options as $option => $value) {
            $this->addFirstOption($option, $value);
        }
    }

    public function addSecondOption(string $option, $value): void
    {
        $options = $this->getOptions()['second'] ?? [];

        $options[$option] = $value;

        $this->addOption('second', $options);
    }

    public function addSecondOptions(array $options): void
    {
        foreach ($options as $option => $value) {
            $this->addSecondOption($option, $value);
        }
    }

    protected function _getType(): string
    {
        return FilterTypeEnum::INTEGER_INTERVAL;
    }
}
