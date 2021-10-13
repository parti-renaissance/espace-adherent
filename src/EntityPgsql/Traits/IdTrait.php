<?php

namespace App\EntityPgsql\Traits;

use Doctrine\ORM\Mapping as ORM;

trait IdTrait
{
    /**
     * @ORM\Column(type="bigint")
     * @ORM\Id
     * @ORM\GeneratedValue
     */
    private ?int $id = null;

    public function getId(): ?int
    {
        return $this->id;
    }
}
