<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Embeddable
 */
class ManagedArea
{
    /**
     * The codes of the managed zones.
     *
     * @var array
     *
     * @ORM\Column(type="simple_array", nullable=true)
     */
    private $codes;

    /**
     * @ORM\Column(type="geo_point", nullable=true)
     */
    private $markerLatitude;

    /**
     * @ORM\Column(type="geo_point", nullable=true)
     */
    private $markerLongitude;

    public function getCodes(): array
    {
        return $this->codes;
    }

    public function setCodes(array $codes)
    {
        $this->codes = $codes;
    }

    public function getCodesAsString(): string
    {
        return implode(', ', $this->codes);
    }

    public function setCodesAsString(string $codes)
    {
        $this->codes = array_map('trim', explode(',', $codes));
    }

    public function getMarkerLatitude()
    {
        return $this->markerLatitude;
    }

    public function setMarkerLatitude($markerLatitude)
    {
        $this->markerLatitude = $markerLatitude;
    }

    public function getMarkerLongitude()
    {
        return $this->markerLongitude;
    }

    public function setMarkerLongitude($markerLongitude)
    {
        $this->markerLongitude = $markerLongitude;
    }
}
