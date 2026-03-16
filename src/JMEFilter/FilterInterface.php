<?php

declare(strict_types=1);

namespace App\JMEFilter;

interface FilterInterface
{
    public function getPosition(): int;

    public function setPosition(int $position): void;
}
