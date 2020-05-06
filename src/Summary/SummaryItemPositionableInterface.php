<?php

namespace App\Summary;

interface SummaryItemPositionableInterface
{
    public function isNew(): bool;

    public function getDisplayOrder(): int;

    public function setDisplayOrder(int $position): void;
}
