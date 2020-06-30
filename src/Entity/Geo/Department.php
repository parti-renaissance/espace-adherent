<?php

namespace App\Entity\Geo;

use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;
use App\Entity\EntityTimestampableTrait;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="geo_department")
 *
 * @Algolia\Index(autoIndex=false)
 */
class Department implements CollectivityInterface
{
    use GeoTrait;
    use EntityTimestampableTrait;

    /**
     * @var Region
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Geo\Region")
     * @ORM\JoinColumn(nullable=false)
     */
    private $region;

    public function __construct(string $code, string $name, Region $region)
    {
        $this->code = $code;
        $this->name = $name;
        $this->region = $region;
    }

    public function getRegion(): Region
    {
        return $this->region;
    }

    public function setRegion(Region $region): void
    {
        $this->region = $region;
    }

    public function getParents(): array
    {
        $parents = [];

        $parents[] = $region = $this->getRegion();
        if ($region) {
            $parents = array_merge($parents, $region->getParents());
        }

        return $this->sanitizeEntityList($parents);
    }
}
