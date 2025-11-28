<?php

declare(strict_types=1);

namespace App\Entity\Geo;

use App\Entity\EntityTimestampableTrait;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'geo_country')]
class Country implements ZoneableInterface
{
    use GeoTrait;
    use EntityTimestampableTrait;

    /**
     * @var ForeignDistrict|null
     */
    #[ORM\ManyToOne(targetEntity: ForeignDistrict::class, inversedBy: 'countries')]
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
        $toMerge = [];

        if ($this->foreignDistrict) {
            $toMerge[] = [$this->foreignDistrict];
            $toMerge[] = $this->foreignDistrict->getParents();
        }

        return $toMerge ? array_values(array_unique(array_merge(...$toMerge))) : [];
    }

    public function getZoneType(): string
    {
        return Zone::COUNTRY;
    }
}
