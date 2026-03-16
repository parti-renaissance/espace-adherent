<?php

declare(strict_types=1);

namespace App\JMEFilter\Layout;

class FilterConfig
{
    public function __construct(
        public string $builderClass,
        public int $position = 100,
    ) {
    }
}
