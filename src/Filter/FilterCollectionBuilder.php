<?php

namespace App\Filter;

use App\Filter\Types\Autocomplete;
use App\Filter\Types\DateInterval;
use App\Filter\Types\DefinedTypes\BooleanSelect;
use App\Filter\Types\Select;
use App\Filter\Types\Text;

/**
 * @method self setChoices(string[] $choices)
 * @method self setUrl(string $url)
 * @method self setQueryParam(string $queryParam)
 * @method self setValueParam(string $valueParam)
 * @method self setLabelParam(string $labelParam)
 * @method self setMultiple(bool $multiple)
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
