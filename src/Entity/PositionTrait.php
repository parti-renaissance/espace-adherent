<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Serializer\Annotation as SymfonySerializer;

trait PositionTrait
{
    /**
     * @var int
     *
     * @ORM\Column(type="smallint", options={"default": 0})
     *
     * @Gedmo\SortablePosition
     *
     * @SymfonySerializer\Groups({
     *     "formation_read",
     *     "formation_list_read",
     *     "formation_write",
     * })
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
