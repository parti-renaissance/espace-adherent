<?php

namespace App\Entity\Geo;

use App\Entity\EntityTimestampableTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\Geo\CityRepository")
 * @ORM\Table(name="geo_city")
 */
class City implements ZoneableInterface
{
    use GeoTrait;
    use EntityTimestampableTrait;

    /**
     * @var string[]|null
     *
     * @ORM\Column(type="simple_array", nullable=true)
     */
    private $postalCode;

    /**
     * @var int|null
     *
     * @ORM\Column(type="integer", nullable=true)
     */
    private $population;

    /**
     * @var Department|null
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Geo\Department")
     * @ORM\JoinColumn(nullable=true)
     */
    private $department;

    /**
     * @var District|null
     *
     * @ORM\ManyToMany(targetEntity="App\Entity\Geo\District", inversedBy="cities", cascade={"persist"})
     * @ORM\JoinTable(name="geo_city_district")
     */
    private $districts;

    /**
     * @var Canton[]|Collection
     *
     * @ORM\ManyToMany(targetEntity="App\Entity\Geo\Canton", inversedBy="cities")
     * @ORM\JoinTable(name="geo_city_canton")
     */
    private $cantons;

    /**
     * @var CityCommunity|null
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Geo\CityCommunity")
     */
    private $cityCommunity;

    public function __construct(string $code, string $name)
    {
        $this->code = $code;
        $this->name = $name;
        $this->districts = new ArrayCollection();
        $this->cantons = new ArrayCollection();
    }

    /**
     * @return string[]
     */
    public function getPostalCode(): array
    {
        return $this->postalCode ?: [];
    }

    /**
     * @param string[] $postalCode
     */
    public function setPostalCode(array $postalCode): void
    {
        $this->postalCode = $postalCode;
    }

    public function getPopulation(): ?int
    {
        return $this->population;
    }

    public function setPopulation(?int $population): void
    {
        $this->population = $population;
    }

    public function getDepartment(): ?Department
    {
        return $this->department;
    }

    public function setDepartment(?Department $department): void
    {
        $this->department = $department;
    }

    public function getDistricts(): array
    {
        return $this->districts->toArray();
    }

    public function addDistrict(?District $district): void
    {
        $this->districts->contains($district) || $this->districts->add($district);
    }

    public function clearDistricts(): void
    {
        $this->districts->clear();
    }

    /**
     * @return Canton[]
     */
    public function getCantons(): array
    {
        return $this->cantons->toArray();
    }

    public function addCanton(Canton $canton): void
    {
        $this->cantons->contains($canton) || $this->cantons->add($canton);
    }

    public function clearCantons(): void
    {
        $this->cantons->clear();
    }

    public function getCityCommunity(): ?CityCommunity
    {
        return $this->cityCommunity;
    }

    public function setCityCommunity(?CityCommunity $cityCommunity): void
    {
        $this->cityCommunity = $cityCommunity;
    }

    public function getParents(): array
    {
        $toMerge = [];

        if ($this->department) {
            $toMerge[] = [$this->department];
            $toMerge[] = $this->department->getParents();
        }

        $toMerge[] = $this->districts->toArray();

        $toMerge[] = $this->cantons->toArray();

        if ($this->cityCommunity) {
            $toMerge[] = [$this->cityCommunity];
        }

        return $toMerge ? array_values(array_unique(array_merge(...$toMerge))) : [];
    }

    public function getZoneType(): string
    {
        return Zone::CITY;
    }
}
