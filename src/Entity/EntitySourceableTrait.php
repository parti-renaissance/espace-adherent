<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

trait EntitySourceableTrait
{
    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    protected ?bool $forRenaissance = false;

    public function isForRenaissance(): ?bool
    {
        return $this->forRenaissance;
    }

    public function setForRenaissance(?bool $forRenaissance): void
    {
        $this->forRenaissance = $forRenaissance;
    }
}
