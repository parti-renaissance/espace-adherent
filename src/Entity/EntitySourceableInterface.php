<?php

namespace App\Entity;

interface EntitySourceableInterface
{
    public function isForRenaissance(): ?bool;

    public function setForRenaissance(?bool $forRenaissance): void;
}
