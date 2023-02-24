<?php

namespace App\Entity\Geo;

use App\Entity\EntityTimestampableTrait;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="geo_vote_place")
 */
class VotePlace implements ZoneableInterface
{
    use GeoTrait;
    use EntityTimestampableTrait;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Geo\City")
     * @ORM\JoinColumn(nullable=false)
     */
    public ?City $city = null;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Geo\District")
     * @ORM\JoinColumn(nullable=false)
     */
    public ?District $district = null;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Geo\Canton")
     */
    public ?Canton $canton = null;

    public function __construct(string $code, string $name, City $city, District $district)
    {
        $this->code = $code;
        $this->name = $name;
        $this->city = $city;
        $this->district = $district;
    }

    public function getParents(): array
    {
        $parents = [];

        foreach (array_filter([$this->city, $this->district, $this->canton]) as $zone) {
            $parents = array_merge($parents, [$zone], $zone->getParents());
        }

        return $parents;
    }

    public function getZoneType(): string
    {
        return Zone::VOTE_PLACE;
    }
}
