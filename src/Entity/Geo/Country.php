<?php

namespace App\Entity\Geo;

use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;
use App\Entity\ConsularDistrict;
use App\Entity\EntityTimestampableTrait;
use App\Entity\ForeignDistrict;
use App\Entity\ZoneInterface;
use App\Entity\ZoneTrait;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="geo_country")
 *
 * @Algolia\Index(autoIndex=false)
 */
class Country implements ZoneInterface
{
    use ZoneTrait;
    use ActivableTrait;
    use EntityTimestampableTrait;

    /**
     * @var ForeignDistrict|null
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\ForeignDistrict", inversedBy="countries")
     */
    private $foreignDistrict;

    /**
     * @var Collection|ConsularDistrict[]
     *
     * @ORM\OneToMany(targetEntity="App\Entity\ConsularDistrict", mappedBy="countries")
     */
    private $consularDistricts;

    public function __construct(string $code, string $name)
    {
        $this->code = $code;
        $this->name = $name;
    }

    public function getParents(): array
    {
        return [];
    }

    public function getForeignDistrict(): ?ForeignDistrict
    {
        return $this->foreignDistrict;
    }

    public function setForeignDistrict(?ForeignDistrict $foreignDistrict): void
    {
        $this->foreignDistrict = $foreignDistrict;
    }

    /**
     * @return ConsularDistrict[]|Collection
     */
    public function getConsularDistricts(): Collection
    {
        return $this->consularDistricts;
    }
}
