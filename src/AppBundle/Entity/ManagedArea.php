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

    public function getMarkerLatitude(): ?string
    {
        return $this->markerLatitude;
    }

    public function setMarkerLatitude(?string $markerLatitude): void
    {
        if (!$markerLatitude) {
            $markerLatitude = null;
        }

        $this->markerLatitude = $markerLatitude;
    }

    public function getMarkerLongitude(): ?string
    {
        return $this->markerLongitude;
    }

    public function setMarkerLongitude(?string $markerLongitude): void
    {
        if (!$markerLongitude) {
            $markerLongitude = null;
        }

        $this->markerLongitude = $markerLongitude;
    }
}
