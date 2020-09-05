<?php

namespace App\Entity;

use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;
use App\Entity\Geo\Country;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ConsularDistrictRepository")
 * @ORM\Table(
 *     uniqueConstraints={@ORM\UniqueConstraint(name="consular_district_code_unique", columns="code")}
 * )
 *
 * @Algolia\Index(autoIndex=false)
 */
class ConsularDistrict implements ZoneInterface
{
    use ZoneTrait;

    /**
     * @var Collection|Country[]
     *
     * @ORM\OneToMany(targetEntity="App\Entity\Geo\Country", mappedBy="consularDistricts")
     */
    private $countries;

    /**
     * @var array
     *
     * @ORM\Column(type="simple_array")
     */
    private $cities;

    /**
     * @var int
     *
     * @ORM\Column(type="smallint")
     */
    private $number;

    /**
     * @var
     *
     * @ORM\Column(type="json", nullable=true)
     */
    private $points;

    /**
     * @var ForeignDistrict
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\ForeignDistrict", inversedBy="consularDistricts")
     */
    private $foreignDistrict;

    public function __construct(
        Collection $countries,
        ForeignDistrict $foreignDistrict,
        array $cities,
        string $code,
        int $number
    ) {
        $this->countries = $countries;
        $this->foreignDistrict = $foreignDistrict;
        $this->cities = $cities;
        $this->code = $code;
        $this->number = $number;
    }

    public function getCountries(): Collection
    {
        return $this->countries;
    }

    public function getCities(): array
    {
        return $this->cities;
    }

    public function setCities(array $cities): void
    {
        $this->cities = $cities;
    }

    public function getPoints(): ?array
    {
        return $this->points;
    }

    public function update(self $district): void
    {
        $this->countries = $district->getCountries();
        $this->foreignDistrict = $district->getForeignDistrict();
        $this->cities = $district->getCities();
        $this->code = $district->getCode();
        $this->points = $district->getPoints();
    }

    public function clearPoints(): void
    {
        $this->points = [];
    }

    public function addPoint(float $latitude, float $longitude, string $label = null): void
    {
        $this->points[] = [
            $latitude,
            $longitude,
            $label,
        ];
    }

    public function getNumber(): int
    {
        return $this->number;
    }

    public function setNumber(int $number): void
    {
        $this->number = $number;
    }

    public function getForeignDistrict(): ForeignDistrict
    {
        return $this->foreignDistrict;
    }

    public function setForeignDistrict(ForeignDistrict $foreignDistrict): void
    {
        $this->foreignDistrict = $foreignDistrict;
    }

    public function getParents(): array
    {
        $parents = [];

        $parents[] = $foreignDistrict = $this->getForeignDistrict();
        if ($foreignDistrict) {
            $parents = array_merge($parents, $foreignDistrict->getParents());
        }

        return $this->sanitizeEntityList($parents);
    }
}
