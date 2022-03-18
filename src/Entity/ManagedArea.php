<?php

namespace App\Entity;

use App\Validator\TagAllInManagedAreaCodes;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\MappedSuperclass
 */
abstract class ManagedArea
{
    /**
     * @var int|null
     *
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue
     */
    private $id;

    /**
     * @var array
     *
     * @ORM\Column(type="simple_array", nullable=true)
     *
     * @TagAllInManagedAreaCodes
     */
    private $codes;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCodes(): ?array
    {
        return $this->codes;
    }

    public function setCodes(array $codes): void
    {
        $this->codes = $codes;
    }

    public function getCodesAsString(): string
    {
        return implode(', ', $this->codes);
    }

    public function setCodesAsString(?string $codes): void
    {
        $this->codes = $codes ? array_map('trim', explode(',', $codes)) : [];
    }

    public function __toString(): string
    {
        return $this->getCodesAsString();
    }
}
