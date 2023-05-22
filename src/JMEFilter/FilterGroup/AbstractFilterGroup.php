<?php

namespace App\JMEFilter\FilterGroup;

use App\JMEFilter\FilterInterface;
use Symfony\Component\Serializer\Annotation\Groups;

class AbstractFilterGroup
{
    protected const LABEL = '';
    protected const COLOR = '';

    /** @Groups("filter:read") */
    public string $label;
    /** @Groups("filter:read") */
    public string $color;

    private array $filters = [];

    final public function __construct()
    {
        $this->label = static::LABEL;
        $this->color = static::COLOR;
    }

    public function addFilter(FilterInterface $filter): void
    {
        $this->filters[] = $filter;
    }

    /** @Groups("filter:read") */
    public function getFilters(): array
    {
        $filters = $this->filters;

        usort($filters, function (FilterInterface $filter1, FilterInterface $filter2) {
            return $filter1->getPosition() <=> $filter2->getPosition();
        });

        return $filters;
    }
}
