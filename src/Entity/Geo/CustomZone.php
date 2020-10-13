<?php

namespace App\Entity\Geo;

use App\Entity\EntityTimestampableTrait;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(readOnly=true)
 * @ORM\Table(name="geo_custom_zone")
 */
class CustomZone implements ZoneableInterface
{
    use GeoTrait;
    use EntityTimestampableTrait;

    public function getParents(): array
    {
        return [];
    }

    public function getZoneType(): string
    {
        return Zone::CUSTOM;
    }
}
