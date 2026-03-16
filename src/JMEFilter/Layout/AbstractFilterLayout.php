<?php

declare(strict_types=1);

namespace App\JMEFilter\Layout;

abstract class AbstractFilterLayout implements FilterLayoutInterface
{
    public function getPriority(): int
    {
        return 0;
    }

    protected function group(string $groupClass, array $filters = [], ?string $labelOverride = null): FilterGroupConfig
    {
        return new FilterGroupConfig($groupClass, 0, $filters, $labelOverride);
    }

    protected function filter(string $builderClass, int $position = 100): FilterConfig
    {
        return new FilterConfig($builderClass, $position);
    }
}
