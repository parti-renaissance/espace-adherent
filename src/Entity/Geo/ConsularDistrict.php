<?php

declare(strict_types=1);

namespace App\Entity\Geo;

use App\Entity\EntityTimestampableTrait;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'geo_consular_district')]
class ConsularDistrict implements ZoneableInterface
{
    use GeoTrait;
    use EntityTimestampableTrait;

    /**
     * @var array
     */
    #[ORM\Column(type: 'simple_array')]
    private $cities = [];

    /**
     * @var int
     */
    #[ORM\Column(type: 'smallint')]
    private $number;

    /**
     * @var ForeignDistrict
     */
    #[ORM\ManyToOne(targetEntity: ForeignDistrict::class)]
    private $foreignDistrict;

    public function __construct(string $code, string $name, int $number, ForeignDistrict $foreignDistrict)
    {
        $this->code = $code;
        $this->name = $name;
        $this->number = $number;
        $this->foreignDistrict = $foreignDistrict;
    }

    public function getNumber(): int
    {
        return $this->number;
    }

    public function getCities(): array
    {
        return $this->cities;
    }

    public function setCities(array $cities): void
    {
        $this->cities = $cities;
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
        return array_merge(
            [$this->foreignDistrict],
            $this->foreignDistrict->getParents(),
            // country isn't a parent from "foreign district" point of view
            // but it's for consultar district
            $this->foreignDistrict->getCountries(),
        );
    }

    public function getZoneType(): string
    {
        return Zone::CONSULAR_DISTRICT;
    }
}
