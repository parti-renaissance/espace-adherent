<?php

namespace App\Entity;

use App\Entity\Geo\Zone;
use Doctrine\ORM\Mapping as ORM;

#[ORM\MappedSuperclass]
abstract class ZoneManagedArea
{
    /**
     * @var int|null
     */
    #[ORM\Column(type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    private $id;

    /**
     * @var Zone|null
     */
    #[ORM\JoinColumn(onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: Zone::class)]
    private $zone;

    /**
     * @var array
     *
     * @deprecated
     */
    #[ORM\Column(type: 'simple_array', nullable: true)]
    private $codes;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getZone(): ?Zone
    {
        return $this->zone;
    }

    public function setZone(?Zone $zone): void
    {
        $this->zone = $zone;
    }

    public function __toString(): string
    {
        return $this->zone;
    }
}
