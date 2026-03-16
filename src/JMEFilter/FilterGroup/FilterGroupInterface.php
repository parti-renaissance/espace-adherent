<?php

declare(strict_types=1);

namespace App\JMEFilter\FilterGroup;

use App\JMEFilter\FilterInterface;

interface FilterGroupInterface
{
    public function addFilter(FilterInterface $filter): void;

    /**
     * @return FilterInterface[]
     */
    public function getFilters(): array;
}
