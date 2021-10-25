<?php

namespace App\Entity\Pap;

use App\Entity\EntityIdentityTrait;
use App\Entity\GeoPointTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

/**
 * @ORM\Entity(repositoryClass="App\Repository\Pap\AddressRepository")
 * @ORM\Table(name="pap_address", indexes={
 *     @ORM\Index(columns={"uuid"}),
 *     @ORM\Index(columns={"offset_x", "offset_y"}),
 *     @ORM\Index(columns={"latitude", "longitude"})
 * })
 */
class Address
{
    use EntityIdentityTrait;
    use GeoPointTrait;

    /**
     * @ORM\Column(nullable=true)
     */
    private ?string $number = null;

    /**
     * @ORM\Column(nullable=true)
     */
    private ?string $address = null;

    /**
     * @ORM\Column(length=5, nullable=true)
     */
    private ?string $inseeCode = null;

    /**
     * @ORM\Column(nullable=true)
     */
    private ?string $cityName = null;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private ?int $offsetX = null;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private ?int $offsetY = null;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Pap\Voter", mappedBy="address", fetch="EXTRA_LAZY")
     */
    private Collection $voters;

    public function __construct(UuidInterface $uuid = null)
    {
        $this->uuid = $uuid ?? Uuid::uuid4();
        $this->voters = new ArrayCollection();
    }

    public function getNumber(): ?string
    {
        return $this->number;
    }

    public function setNumber(?string $number): void
    {
        $this->number = $number;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(?string $address): void
    {
        $this->address = $address;
    }

    public function getInseeCode(): ?string
    {
        return $this->inseeCode;
    }

    public function setInseeCode(?string $inseeCode): void
    {
        $this->inseeCode = $inseeCode;
    }

    public function getCityName(): ?string
    {
        return $this->cityName;
    }

    public function setCityName(?string $cityName): void
    {
        $this->cityName = $cityName;
    }

    public function getOffsetX(): ?int
    {
        return $this->offsetX;
    }

    public function setOffsetX(?int $offsetX): void
    {
        $this->offsetX = $offsetX;
    }

    public function getOffsetY(): ?int
    {
        return $this->offsetY;
    }

    public function setOffsetY(?int $offsetY): void
    {
        $this->offsetY = $offsetY;
    }

    public function getVoters(): Collection
    {
        return $this->voters;
    }

    public function setVoters($voters): void
    {
        $this->voters = $voters;
    }
}
