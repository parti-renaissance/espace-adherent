<?php

namespace App\Entity\Geo;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="geo_vote_place")
 */
class VotePlace
{
    use GeoTrait;

    /**
     * @var string|null
     *
     * @ORM\Column(nullable=true)
     */
    private $cityName;

    /**
     * @var string|null
     *
     * @ORM\Column(nullable=true)
     */
    private $inseeCode;

    /**
     * @var string|null
     *
     * @ORM\Column(nullable=true)
     */
    private $postalCode;

    /**
     * @var string|null
     *
     * @ORM\Column(nullable=true)
     */
    private $address;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(type="date", nullable=true)
     */
    private $closedAt;

    /**
     * @var Zone[]|Collection
     *
     * @ORM\ManyToMany(targetEntity="App\Entity\Geo\Zone")
     * @ORM\JoinTable(name="geo_vote_place_zone")
     */
    private $zones;

    public function __construct()
    {
        $this->zones = new ArrayCollection();
    }

    public function getCityName(): ?string
    {
        return $this->cityName;
    }

    public function setCityName(?string $cityName): void
    {
        $this->cityName = $cityName;
    }

    public function getInseeCode(): ?string
    {
        return $this->inseeCode;
    }

    public function setInseeCode(?string $inseeCode): void
    {
        $this->inseeCode = $inseeCode;
    }

    public function getPostalCode(): ?string
    {
        return $this->postalCode;
    }

    public function setPostalCode(?string $postalCode): void
    {
        $this->postalCode = $postalCode;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(?string $address): void
    {
        $this->address = $address;
    }

    public function getClosedAt(): ?\DateTime
    {
        return $this->closedAt;
    }

    public function setClosedAt(?\DateTime $closedAt): void
    {
        $this->closedAt = $closedAt;
    }

    public function getZones(): Collection
    {
        return $this->zones;
    }

    public function setZones($zones): void
    {
        $this->zones = $zones;
    }
}
