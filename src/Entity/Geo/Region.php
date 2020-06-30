<?php

namespace App\Entity\Geo;

use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;
use App\Entity\EntityTimestampableTrait;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="geo_region")
 *
 * @Algolia\Index(autoIndex=false)
 */
class Region
{
    use GeoTrait;
    use EntityTimestampableTrait;

    /**
     * @var Country|null
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Geo\Country")
     * @ORM\JoinColumn(nullable=false)
     */
    private $country;

    public function __construct(string $code, string $name, Country $country)
    {
        $this->code = $code;
        $this->name = $name;
        $this->country = $country;
    }

    public function getCountry(): ?Country
    {
        return $this->country;
    }

    public function setCountry(Country $country): void
    {
        $this->country = $country;
    }
}
