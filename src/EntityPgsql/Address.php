<?php

namespace App\EntityPgsql;

use App\EntityPgsql\Traits\IdTrait;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class Address
{
    use IdTrait;

    /**
     * @ORM\Column
     */
    private ?string $number = null;

    /**
     * @ORM\Column
     */
    private ?string $street = null;

    public function getNumber(): ?string
    {
        return $this->number;
    }

    public function setNumber(?string $number): void
    {
        $this->number = $number;
    }

    public function getStreet(): ?string
    {
        return $this->street;
    }

    public function setStreet(?string $street): void
    {
        $this->street = $street;
    }
}
