<?php

namespace App\Entity\Geo;

use Doctrine\ORM\Mapping as ORM;

trait ActivableTrait
{
    /**
     * @var bool
     *
     * @ORM\Column(type="boolean")
     */
    private $active = true;

    public function isActive(): bool
    {
        return $this->active;
    }

    public function activate(bool $active = true): void
    {
        $this->active = $active;
    }
}
