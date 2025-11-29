<?php

declare(strict_types=1);

namespace App\JMEFilter\Types;

use App\JMEFilter\FilterTypeEnum;

class Select extends AbstractFilter
{
    private bool $withEmptyChoice = false;
    private string $emptyChoiceLabel = 'Aucune sélection';

    public function setMultiple(bool $value): void
    {
        $this->addOption('multiple', $value);
    }

    public function setChoices(array $choices): void
    {
        if ($this->withEmptyChoice) {
            $choices = array_merge(['' => $this->emptyChoiceLabel], $choices);
        }

        $this->addOption('choices', $choices);
    }

    public function withEmptyChoice(bool $value, ?string $label = null): void
    {
        $this->withEmptyChoice = $value;
        $this->emptyChoiceLabel = $label ?? $this->emptyChoiceLabel;

        $this->setChoices($this->getOptions()['choices'] ?? []);
    }

    protected function _getType(): string
    {
        return FilterTypeEnum::SELECT;
    }
}
