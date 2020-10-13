<?php

namespace App\Entity\Geo;

use App\Entity\EntityTimestampableTrait;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="geo_country")
 */
class Country implements ZoneableInterface
{
    use GeoTrait;
    use EntityTimestampableTrait;

    /**
     * @var ForeignDistrict|null
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Geo\ForeignDistrict", inversedBy="countries")
     */
    private $foreignDistrict;

    public function __construct(string $code, string $name)
    {
        $this->code = $code;
        $this->name = $name;
    }

    public function getForeignDistrict(): ?ForeignDistrict
    {
        return $this->foreignDistrict;
    }

    public function setForeignDistrict(?ForeignDistrict $foreignDistrict): void
    {
        $this->foreignDistrict = $foreignDistrict;
    }

    public function getParents(): array
    {
        $toMerge = [
            [$this->foreignDistrict],
            $this->foreignDistrict->getParents(),
        ];

        return array_values(array_unique(array_merge(...$toMerge)));
    }

    public function getZoneType(): string
    {
        return Zone::COUNTRY;
    }
}
