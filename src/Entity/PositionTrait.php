<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

trait PositionTrait
{
    /**
     * @var int
     *
     * @ORM\Column(type="smallint", options={"unsigned": true, "default": 0})
     */
    private $position = 0;

    public function getPosition(): int
    {
        return $this->position;
    }

    public function setPosition(int $position): void
    {
        $this->position = $position;
    }
}
