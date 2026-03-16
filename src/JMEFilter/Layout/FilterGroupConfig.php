<?php

declare(strict_types=1);

namespace App\JMEFilter\Layout;

class FilterGroupConfig
{
    public function __construct(
        public string $groupClass,
        public int $position,
        public array $filters = [],
        public ?string $labelOverride = null,
    ) {
    }
}
