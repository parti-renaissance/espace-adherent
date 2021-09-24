<?php

namespace App\Filter\Types;

use App\Filter\FilterTypeEnum;

class Select extends AbstractFilter
{
    public function setMultiple(bool $value): void
    {
        $this->addOption('multiple', $value);
    }

    public function setChoices(array $choices): void
    {
        $this->addOption('choices', $choices);
    }

    protected function _getType(): string
    {
        return FilterTypeEnum::SELECT;
    }
}
