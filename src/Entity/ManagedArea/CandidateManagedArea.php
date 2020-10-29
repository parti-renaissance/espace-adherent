<?php

namespace App\Entity\ManagedArea;

use App\Entity\Geo\Zone;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class CandidateManagedArea
{
    /**
     * @var int|null
     *
     * @ORM\Id
     * @ORM\Column(type="integer", options={"unsigned": true})
     * @ORM\GeneratedValue
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Geo\Zone", cascade={"persist"})
     */
    private $zone;

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
}
