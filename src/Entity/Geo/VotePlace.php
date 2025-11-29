<?php

declare(strict_types=1);

namespace App\Entity\Geo;

use App\Entity\EntityTimestampableTrait;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'geo_vote_place')]
class VotePlace implements ZoneableInterface
{
    use GeoTrait;
    use EntityTimestampableTrait;

    #[ORM\JoinColumn(nullable: false)]
    #[ORM\ManyToOne(targetEntity: City::class)]
    public ?City $city = null;

    #[ORM\JoinColumn(nullable: false)]
    #[ORM\ManyToOne(targetEntity: District::class)]
    public ?District $district = null;

    #[ORM\ManyToOne(targetEntity: Canton::class)]
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
