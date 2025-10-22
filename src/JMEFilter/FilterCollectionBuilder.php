<?php

namespace App\JMEFilter;

use App\JMEFilter\Types\Autocomplete;
use App\JMEFilter\Types\DateInterval;
use App\JMEFilter\Types\DefinedTypes\BooleanSelect;
use App\JMEFilter\Types\Select;
use App\JMEFilter\Types\Text;

/**
 * @method self setPosition(int $position)
 * @method self setChoices(string[] $choices)
 * @method self setPlaceholder(?string $placeholder)
 * @method self setUrl(string $url)
 * @method self setQueryParam(string $queryParam)
 * @method self setValueParam(string $valueParam)
 * @method self setLabelParam(string $labelParam)
 * @method self setMultiple(bool $multiple)
 * @method self withEmptyChoice(bool $value, ?string $label)
 * @method self setRequired(bool $value)
 * @method self setHelp(?string $message)
 * @method self setFavorite(bool $value)
 * @method self setAdvanced(bool $value)
 */
class FilterCollectionBuilder
{
    private array $filters = [];
    private ?FilterInterface $current;

    public function createFrom(string $class, array $options = []): self
    {
        return $this->create(new $class($options));
    }

    public function createText(string $code, string $label): self
    {
        return $this->create(new Text($code, $label));
    }

    public function createSelect(string $code, string $label): self
    {
        return $this->create(new Select($code, $label));
    }

    public function createBooleanSelect(string $code, string $label): self
    {
        return $this->create(new BooleanSelect($code, $label));
    }

    public function createDateInterval(string $code, string $label): self
    {
        return $this->create(new DateInterval($code, $label));
    }

    public function createAutocomplete(string $code, string $label): self
    {
        return $this->create(new Autocomplete($code, $label));
    }

    public function getFilters(): array
    {
        return $this->filters;
    }

    public function __call(string $methodName, array $arguments): self
    {
        if ($this->current) {
            if (method_exists($this->current, $methodName)) {
                $this->current->$methodName(...$arguments);

                return $this;
            }
        }

        throw new \InvalidArgumentException();
    }

    private function create(FilterInterface $filter): self
    {
        $this->filters[] = $this->current = $filter;

        return $this;
    }
}
