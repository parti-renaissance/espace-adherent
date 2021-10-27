<?php

namespace App\Entity;

trait GeoPointTrait
{
    /**
     * @var float|null
     *
     * @ORM\Column(type="geo_point", nullable=true)
     */
    private $latitude;

    /**
     * @var float|null
     *
     * @ORM\Column(type="geo_point", nullable=true)
     */
    private $longitude;

    public function getLatitude(): ?float
    {
        return $this->latitude;
    }

    public function setLatitude(?float $latitude): void
    {
        $this->latitude = $latitude;
    }

    public function getLongitude(): ?float
    {
        return $this->longitude;
    }

    public function setLongitude(?float $longitude): void
    {
        $this->longitude = $longitude;
    }
}
